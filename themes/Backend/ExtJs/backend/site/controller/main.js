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
 * Shopware UI - Site main Controller
 *
 * This file handles the main window.
 */

//{namespace name=backend/site/site}

//{block name="backend/site/controller/main"}
Ext.define('Shopware.apps.Site.controller.Main', {

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
        { ref:'parentIdField', selector:'site-form hidden[name=parentId]' },
        { ref:'navigationTree', selector:'site-tree' },
        { ref:'deleteSiteButton', selector:'site-mainWindow button[action=onDeleteSite]' },
        { ref:'saveSiteButton', selector:'site-form button[action=onSaveSite]' }
    ],

    /**
     * Creates the necessary event listeners for this
     * controller and the main window
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.subApplication.nodeStore = me.subApplication.getStore('Nodes');

        me.groupStore = me.subApplication.getStore('Groups');
        me.selectedStore = me.subApplication.getStore('Selected');
        me.shopStore = me.subApplication.getStore('Shop').load();

        me.mainWindow = me.getView('main.Window').create({
            nodeStore: me.subApplication.nodeStore,
            groupStore: me.groupStore,
            selectedStore: me.selectedStore,
            shopStore: me.shopStore
        });

        me.control({
            //fires, when the user tries to create a new site
            'site-mainWindow button[action=onCreateSite]': {
                click: me.onCreateSite
            },
            //fires, when the user tries to delete a site
            'site-mainWindow button[action=onDeleteSite]': {
                click: me.onDeleteSite
            }
        });

        me.callParent(arguments);
    },

    /**
     * Event listener method which is called when the onCreateSite is fired.
     * It'll reset the whole form, enabling the user to create a new site from scratch.
     */
    onCreateSite: function() {
        var me = this,
            form = me.getDetailForm().getForm(),
            detailForm = me.getDetailForm(),
            ddselector = detailForm.down('ddselector'),
            tree = me.getNavigationTree(),
            saveSiteButton = me.getSaveSiteButton(),
            record = Ext.create('Shopware.apps.Site.model.Nodes'),
            data = tree.getSelectionModel().hasSelection() ? tree.getSelectionModel().getSelection()[0].data : {};

        //if the current selection is not a root note (like gLeft)
        if ((data.parentId) && (data.parentId !== 'root')) {

            //get parentName and parentId
            var parentName = data.description,
                parentId = data.helperId;

            //ask if the user wants to create a subSite of the currently selected one
            Ext.Msg.confirm('{s name=onCreateNewSiteConfirmationBoxCaption}Create subpage?{/s}', Ext.String.format('{s name=onCreateNewSiteConfirmationBoxText}Are you sure you want to create a subpage of \'[0]\'?{/s}', parentName), function(btn){
                if (btn === 'yes'){
                    ddselector.toStore.removeAll();
                    ddselector.fromStore.load();
                    form.reset();
                    form.loadRecord(record);

                    //set the parentId so we know this will be a subSite
                    me.getParentIdField().setValue(parentId);
                } else {
                    ddselector.toStore.removeAll();
                    ddselector.fromStore.load();
                    form.reset();
                    form.loadRecord(record);
                }
                /*{if {acl_is_allowed privilege=createSite}}*/
                saveSiteButton.enable();
                /*{else}*/
                saveSiteButton.disable();
                /*{/if}*/
            });
        } else {
            ddselector.toStore.removeAll();
            ddselector.fromStore.load();
            form.reset();
            form.loadRecord(record);
        }
    },

    /**
     * Event listener method which is called when the onDeleteSite is fired.
     * A confirmation dialog will open, which will in turn send an ajax request containing the sites id to the site php controller.
     */
    onDeleteSite: function() {
        var me = this,
            tree = me.getNavigationTree(),
            siteName = tree.getSelectionModel().getSelection()[0].data.description,
            siteId = tree.getSelectionModel().getSelection()[0].data.helperId;

        //open confirmation box, ask if the user really wants to delete the selected site
        //if yes, delete site with id and reload store
        Ext.Msg.confirm('{s name=onDeleteSiteConfirmationBoxCaption}Delete site?{/s}', Ext.String.format('{s name=onDeleteSiteConfirmationBoxText}Are you sure you want to delete \'[0]\'?{/s}', siteName), function(btn){
            if (btn === 'yes'){
                Ext.Ajax.request({
                    url : '{url action=deleteSite}',
                    scope: me,
                    params: {
                        siteId: siteId
                    },
                    success: function(response){
                        me.getStore('Nodes').load();
                        tree.getSelectionModel().deselectAll();
                        me.getDetailForm().getForm().reset();
                        Shopware.Notification.createGrowlMessage('','{s name=onDeleteSiteSuccess}The Site has been deleted successfully.{/s}', '{s name=mainWindowTitle}{/s}');
                    },
                    failure: function(response) {
                        var errorMsg = response.proxy.reader.jsonData.message;
                        Shopware.Notification.createGrowlMessage('','{s name=onDeleteSiteError}An error has occurred while trying to delete the site: {/s}' + errorMsg, '{s name=mainWindowTitle}{/s}');
                    }
                });
            }
        });
    }
});
//{/block}
