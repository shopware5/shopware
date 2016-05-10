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
 * @package    Banner
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/banner/controller/main}*/

/**
 * Shopware UI - Banner Controller Main
 *
 * Features the business logic for the banner module.
 */
//{block name="backend/banner/controller/main"}
Ext.define('Shopware.apps.Banner.controller.Main', {
    extend : 'Ext.app.Controller',
    refs: [
        { ref:'addBannerButton', selector:'banner-view-main-panel button[action=addBanner]' },
        { ref:'deleteBannerButton', selector:'banner-view-main-panel button[action=deleteBanner]' },
        { ref:'editBannerButton', selector:'banner-view-main-panel button[action=editBanner]' },
        { ref:'imageViewItem', selector:'banner-view-main-panel panel dataview' },
        { ref:'categoryTree', selector:'banner-view-main-panel treepanel' },
        { ref:'mainPanel', selector:'bannermanager banner-view-main-panel' }
    ],

    /**
     * keeps the message that will be shown if some banners should be deleted
     * @private
     * @string
     */
    deleteDialogMessage: '{s name=delete_dialog_message}There have been [0] banners selected for deletion. Are you sure you want to delete those banners?{/s}',
    /**
     * Holder property for the main panel
     *
     * @private
     * @null
     */
    panel: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application.
     */
    init: function () {
        var me = this;

        // Set necessary event listeners
        me.control({
            'banner-view-main-panel treepanel':{
                itemclick: me.onTreeClick
            },
            'banner-view-main-panel panel dataview':{
              /*{if {acl_is_allowed privilege=update}}*/
                itemdblclick: me.onBannerClick,
              /* {/if} */
                selectionchange: me.onBannerSelection
            },
            'banner-view-main-panel button[action=addBanner]':{
                click: me.onAddBanner
            },
            'banner-view-main-panel button[action=editBanner]':{
                click: me.onEditClick
            },
            'banner-view-main-panel button[action=deleteBanner]':{
                click: me.onDeleteBanner
            },
            //The save-button from the edit-window
            'window button[action=saveBannerEdit]': {
                click: me.onSaveEditBanner
            },
            //The save-button from the add-window
            'window button[action=addBannerSave]': {
                click: me.onSaveEditBanner
            }
        });
        // create and save the new view so we can access that view easily later

        me.subApplication.bannerStore = me.subApplication.getStore('Banner');
        me.subApplication.categoryStore = me.subApplication.getStore('Category');

        Ext.suspendLayouts();
        me.panel = this.subApplication.getView('main.Panel').create({
            categoryStore: me.subApplication.categoryStore,
            bannerStore: me.subApplication.bannerStore
        });

        // Create an show the applications main view.
        me.main = this.subApplication.getView('Main').create({
            items: [ me.panel ]
        }).show();
        Ext.resumeLayouts(true);
    },

    /**
     * Edit method called through the edit button
     *
     * @event click
     */
    onEditClick : function() {
        var me = this,
            bannerStore = me.subApplication.bannerStore,
            categoryStore   = me.subApplication.categoryStore,
            dataView        = me.getMainPanel().dataView,
            selection       = dataView.getSelectionModel().getLastSelected(),
            categoryId      = selection.get('categoryId'),
            currentCategory = categoryStore.getNodeById(categoryId);

        me.getView('main.BannerForm').create({
            bannerStore : bannerStore,
            record      : selection,
            scope       : me,
            categoryId  : categoryId,
            title       : currentCategory.get('text')
        });
    },

    /**
     * Event listener method which will be fired when the user
     * clicks the "save"-button in the "edit banner"-window.
     *
     * Updates an existing banner in the database (server side).
     *
     * @event click
     * @param [object] btn - pressed Ext.button.Button
     * @return void
     */
    onSaveEditBanner : function(btn) {
        var win     = btn.up('window'),
            form    = win.down('form'),
            attributeForm = win.down('shopware-attribute-form'),
            formBasis = form.getForm(),
            me      = this,
            store   = me.subApplication.bannerStore,
            record  = form.getRecord();

        form.getForm().updateRecord(record);

        if (formBasis.isValid()) {
            record.save({
                callback: function(self, operation) {
                    var response = Ext.JSON.decode(operation.response.responseText);
                    var data = response.data;
                    attributeForm.saveAttribute(data.id);

                    Shopware.Msg.createGrowlMessage('', '{s name=saved_success}Banner has been saved.{/s}', '{s name=main_title}{/s}');
                    win.close();
                    store.load({
                        params: { categoryId : record.get('categoryId') }
                    });
                }
            });
        }
        //todo@all Should we display a warning here?
    },

    /**
     * Event listener method which will be fired when the user
     * clicks the "add"-button in the "main"-window.
     *
     * Shows the add-new Banner window
     *
     * @event click
     * @param [object] btn - pressed Ext.button.Button
     * @return void
     */
    onAddBanner : function() {
        var me              = this,
            bannerStore     = me.subApplication.bannerStore,
            categoryStore   = me.subApplication.categoryStore,
            catTree         = me.getCategoryTree(),
            record          = catTree.getSelectionModel().getLastSelected(),
            categoryId      = record.data.id,
            model = Ext.create('Shopware.apps.Banner.model.BannerDetail'),
            currentCategory = categoryStore.getNodeById(categoryId);

        me.getView('main.BannerFormAdd').create({
            bannerStore: bannerStore,
            record: model,
            categoryId: categoryId,
            category: currentCategory
        });
    },

    /**
     * Event listener method which will be fired when the user clicks
     * on the "delete marked banner(s)"-button.
     *
     * Deletes one or multiple banners using a bulk data operation.
     *
     * @event click
     * @return void
     */
    onDeleteBanner : function() {
        var me              = this,
            dataView        = me.getMainPanel().dataView,
            selection       = dataView.getSelectionModel().getSelection(),
            store           = me.subApplication.bannerStore,
            noOfElements    = selection.length;

        Ext.MessageBox.confirm('{s name=delete_dialog_title}Delete selected banners.{/s}',
            Ext.String.format(this.deleteDialogMessage, noOfElements),
            function (response) {
                if ('yes' !== response) {
                    return false;
                }
                if (selection.length > 0) {
                    store.remove(selection);
                    try {
                        Shopware.Msg.createGrowlMessage('', '{s name=delete_success}Banner has been deleted.{/s}', '{s name=main_title}{/s}');
                        store.save();
                        store.load();
                    } catch (e) {
                        Shopware.Msg.createGrowlMessage('', '{s name=delete_error}Not every banner could be deleted:{/s} ' + e.message, '{s name=main_title}{/s}');
                    }
                }
        });
    },

    /**
     * Event listener method which will be fired when the user double
     * clicks an existing banner.
     *
     * Opens the "edit banner" window and passes the associated banner record
     *
     * @event dblclick
     * @param [object] node - HTML DOM node of the clicked banner
     * @param [object] record - Associated Ext.data.Model
     * @return void
     */
    onBannerClick : function(node, record) {
        var me              = this,
            bannerStore     = me.subApplication.bannerStore,
            categoryStore   = me.subApplication.categoryStore,
            categoryId      = record.get('categoryId'),
            currentCategory = categoryStore.getNodeById(categoryId);

        me.getView('main.BannerForm').create({
            bannerStore : bannerStore,
            record      : record,
            scope       : me,
            categoryId  : categoryId,
            title       : currentCategory.get('text')
        });
    },

    /**
     * Event listener method which will be fired when the user
     * clicks on a leaf in the category tree on the left hand.
     *
     * Reloads the associated banner store to match the selected
     * category.
     *
     * @event itemclick
     * @param [object] node - HTML DOM node of the clicked leaf
     * @param [object] record - Associated Ext.data.Model
     * @return void
     */
    onTreeClick : function(node, record) {
        var me          = this,
            bannerStore = me.subApplication.bannerStore,
            categoryId  = record.get('id'),
            bannerBtn   =  me.getAddBannerButton();

        // remove the old filter and set a new one
        bannerStore.clearFilter(true);
        bannerStore.filter("categoryId", categoryId);
        bannerStore.load({
            params: { categoryId: categoryId }
        });

/*{if {acl_is_allowed privilege=create}}*/
        bannerBtn.setDisabled(false);
/* {/if} */

    },

    /**
     * Event listener method which will be fired when the user
     * selects one or more banner.
     *
     * Locks/Unlocks the "delete marked banner(s)"-button
     *
     * @event selectionchange
     * @param [object] view - Ext.view.View
     * @param [array] selection - Array of Ext.data.Model's from the selected banners
     * @return void
     */
    onBannerSelection: function(view, selection) {
        var me          = this,
            deleteBtn   = me.getDeleteBannerButton(),
            editButton = me.getEditBannerButton();
/*{if {acl_is_allowed privilege=delete}}*/
        deleteBtn.setDisabled((selection.length > 0) ? false : true);
/* {/if} */
/*{if {acl_is_allowed privilege=update}}*/
        // rule on when the edit button should be enabled.
        editButton.setDisabled((selection.length == 1) ? false : true);
/* {/if} */

    }
});
//{/block}
