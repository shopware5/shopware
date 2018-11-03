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
 * @package    Site
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Site form Controller
 *
 * This file handles creation and saving of the detail form.
 */

//{namespace name=backend/site/site}

//{block name="backend/site/controller/form"}
Ext.define('Shopware.apps.Site.controller.Form', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Define references for the different parts of the application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * Example: { ref : 'grid', selector : 'grid' } transforms to this.getGrid();
     *          { ref : 'addBtn', selector : 'button[action=add]' } transforms to this.getAddBtn()
     *
     * @object
     */
    refs:[
        { ref:'mainWindow', selector:'site-mainWindow' },
        { ref:'confirmationBox', selector:'site-confirmationBox' },
        { ref:'detailForm', selector:'site-form' },
        { ref:'navigationTree', selector:'site-tree' },
        { ref:'itemSelector', selector:'site-form itemselector' },
        { ref:'navigationTree', selector:'site-tree' },
        { ref:'attributeForm', selector: 'site-mainWindow shopware-attribute-form' },
        { ref:'parentIdField', selector:'site-form hidden[name=parentId]' },
        { ref:'helperIdField', selector:'site-form hidden[name=helperId]' }
    ],

    /**
     * Creates the necessary event listener for this
     * controller and the detail form
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            //fires when the user tries to save the current form state
            'site-form button[action=onSaveSite]': {
                click: me.onSaveSite
            }
        });

        me.callParent(arguments);
    },

    /**
     * Event listener method which is called when the onSaveSite event was fired.
     * It'll get all of the forms values and then call model.save().
     */
    onSaveSite: function() {
        var me = this,
            form = me.getDetailForm(),
            ddselector = form.down('ddselector'),
            toStore = ddselector.toStore,
            values = form.getValues(),
            record = form.getRecord(),
            model;

        form.getForm().updateRecord(record);

        model = record;

        //return if no description or grouping is set
        if (values.description == "" || toStore.first() == null) {
            Shopware.Notification.createGrowlMessage('','{s name=onSaveGroupAndDescriptionNeeded}You need to select a title and a group{/s}','{s name=mainWindowTitle}{/s}');
            return;
        }
        var grouping;
        Ext.each(toStore.data.items, function(item){
            if(grouping){
                grouping = grouping + ',' + item.get('templateVariable');
            }else{
                grouping = item.get('templateVariable');
            }
        });
        model.set('grouping', grouping);

        //if a link is given and no target is explicitly set, set it to _blank
        if (values.link != "" && values.target == "") {
            values.target = "_blank";
        }

        var selectionModel = me.getNavigationTree().getSelectionModel(),
            data;

        if ((selectionModel.lastSelected) && (selectionModel.getSelection()[0].parentNode)) {
            data = selectionModel.getSelection()[0].parentNode.data;

            //if it's a nested site
            model.set('parentId', ~~(values.parentId) || ~~(data.helperId));
        }

        //save the current form state
        model.save({
            success: function(record,response) {
                var responseObject = Ext.decode(response.response.responseText);
                record.set('helperId', responseObject.data.id);
                me.getAttributeForm().saveAttribute(record.get('helperId'));

                Shopware.app.Application.fireEvent('site-save-successfully', me, record, form);

                form.loadRecord(record);
                Shopware.Notification.createGrowlMessage('','{s name=onSaveSiteSuccess}The site has been saved successfully.{/s}', '{s name=mainWindowTitle}{/s}');
                me.getStore('Nodes').load();
            },
            failure: function(response) {
                //get the responseObject
                var responseObject = Ext.decode(response.data),
                    errorMsg = responseObject.message;
                Shopware.Notification.createGrowlMessage('','{s name=onSaveSiteError}An error has occurred while trying to save the site: {/s}' + errorMsg,'{s name=mainWindowTitle}{/s}');
            }
        });
    }
});
//{/block}
