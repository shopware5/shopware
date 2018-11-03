/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Premium
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/premium/main}

/**
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.Premium.controller.Premium', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.app.Controller',

    /**
    * Creates the necessary event listener for this
    * specific controller and opens a new Ext.window.Window
    * @return void
    */
    init: function() {
        var me = this;

        me.control({
            'premium-main-list textfield[action=searchPremiumArticle]':{
                fieldchange: me.onSearch
            },
            //The add-button on the toolbar
            'premium-main-list button[action=add]':{
                click: me.onOpenCreateWindow
            },
            //The delete-button on the toolbar
            'premium-main-list button[action=deleteMultipleArticles]':{
                click: me.onDeleteMultipleArticles
            },
            // The save-button from the create-window
            'window button[action=savePremium]': {
                click: me.onCreatePremium
            },
            'premium-main-list':{
                deleteColumn: me.onDeleteSingleArticle,
                editColumn: me.onOpenEditWindow
            }
        });

    },

   /**
    * Opens the detail-window
    * @event click
    * @return void
    */
    onOpenCreateWindow: function(){
        this.getView('premium.Detail').create();
    },

    /**
     * The user wants to edit an article
     * @event render
     * @param [object] view Contains the view
     * @param [object] item Contains the clicked item
     * @param [int] rowIndex Contains the row-index
     * @return void
     */
    onOpenEditWindow : function (view, item, rowIndex) {
        var store = this.subApplication.premiumStore,
            record = store.getAt(rowIndex);

        //Create edit-window
        this.getView('premium.Detail').create({ record : record, mainStore : store });
    },

    /**
     * Function to create a new article
     * @event click
     * @param [object] btn Contains the clicked button
     * @return void
     */
    onCreatePremium: function(btn) {
        var win = btn.up('window'),
            form = win.down('form'),
            values = form.getForm().getValues(),
            store = this.subApplication.premiumStore;

        var model = Ext.create('Shopware.apps.Premium.model.Premium', values);

        win.close();
        model.save({
            callback: function(data, operation){
                var records = operation.getRecords(),
                    record = records[0],
                    rawData = record.getProxy().getReader().rawData;
                if(operation.success){
                    Shopware.Notification.createGrowlMessage('{s name=growlMessage_title/createPremiumSuccess}The article was successfully created{/s}', "{s name=growlMessage_message/createPremiumSuccess}The article was successfully saved{/s}", '{s name=window_title}{/s}');
                }else{
                    Shopware.Notification.createGrowlMessage('{s name=growlMessage/error}An error has occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
                }
                store.load();
            }
        });
    },

    /**
     * Function to delete multiple articles
     * Every marked article will be deleted
     * @event click
     * @param [object] btn Contains the clicked button
     * @return [boolean|null]
     */
    onDeleteMultipleArticles: function(btn){
        var win = btn.up('window'),
                grid = win.down('grid'),
                selModel = grid.selModel,
                store = grid.getStore(),
                selection = selModel.getSelection(),
                me = this,
                message = Ext.String.format('{s name=messagebox_multipleDelete/message}You have marked [0] articles. Are you sure you want to delete them?{/s}', selection.length);

        //Create a message-box, which has to be confirmed by the user
        Ext.MessageBox.confirm('{s name=messagebox_multipleDelete/title}Delete articles{/s}', message, function (response){
            //If the user doesn't want to delete the articles
            if (response !== 'yes')
            {
                return false;
            }

            Ext.each(selection, function(item){
                store.remove(item);
            });
            store.sync({
                callback: function(batch, operation) {
                    var rawData = batch.proxy.getReader().rawData;
                    if (rawData.success) {
                        me.subApplication.premiumStore.load();
                        Shopware.Notification.createGrowlMessage('{s name=growlMessage_title/deleteMultipleSuccess}Articles deleted{/s}', "{s name=growlMessage_message/deleteMultipleSuccess}The articles were successfully deleted{/s}", '{s name=window_title}{/s}');
                    }else{
                        Shopware.Notification.createGrowlMessage('{s name=growlMessage_title/deleteMultipleError}An error occurred{/s}', rawData.errorMsg, '{s name=window_title}{/s}');
                    }
                }
            })
        });
    },

    /**
     * Function to delete one single article
     * Is used, when the user clicks on the delete-button in the action-column
     * @event click
     * @param [object] view Contains the view
     * @param [int] rowIndex Contains the row-index
     * @return mixed
     */
    onDeleteSingleArticle: function(view, rowIndex){
        var store = this.subApplication.premiumStore,
            values = store.data.items[rowIndex].data,
            message = Ext.String.format('{s name=messagebox_singleDelete/message}Are you sure you want to delete <b> [0] </b> ?{/s}', values.name);

        //Create a message-box, which has to be confirmed by the user
        Ext.MessageBox.confirm('{s name=messagebox_singleDelete/title}Delete article{/s}', message, function (response){
            //If the user doesn't want to delete the article
            if(response != 'yes')
            {
                return false;
            }
            var model = Ext.create('Shopware.apps.Premium.model.Premium', values);
            model.destroy({
                callback: function(){
                    store.load();
                }
            });
        });

    },

    /**
     * @event fieldchange
     * Function to search for articles by using a store-filter
     * @param [object] field Contains the searchfield
     * @return void
     */
    onSearch: function(field){
        var me = this,
            store = me.subApplication.premiumStore;

        //If the search-value is empty, reset the filter
        if(field.getValue().length == 0){
            store.clearFilter();
        }else{
            //This won't reload the store
            store.filters.clear();
            //Loads the store with a special filter
            store.filter('searchValue',field.getValue());
        }
    }
});
