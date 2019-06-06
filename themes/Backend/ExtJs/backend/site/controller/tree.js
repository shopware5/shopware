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
 * This file handles the navigation tree containing the actual sites.
 */

//{namespace name=backend/site/site}

//{block name="backend/site/controller/tree"}
Ext.define('Shopware.apps.Site.controller.Tree', {

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
        { ref:'attributeForm', selector: 'site-mainWindow shopware-attribute-form' },
        { ref:'navigationTree', selector:'site-tree' },
        /*{if {acl_is_allowed privilege=deleteGroup}}*/
        { ref:'deleteGroupButton', selector:'site-tree button[action=onDeleteGroup]' },
        /*{/if}*/
        { ref:'saveSiteButton', selector:'site-form button[action=onSaveSite]' },
        /*{if {acl_is_allowed privilege=deleteSite}}*/
        { ref:'deleteSiteButton', selector:'site-mainWindow button[action=onDeleteSite]' },
        /*{/if}*/
        { ref:'groupSelector', selector:'site-form itemselector[name=grouping]' }
    ],

    /**
     * Creates the necessary event listener for this
     * controller and the main window
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            //fires, when the user tries to create a new group
            'site-tree button[action=onCreateGroup]': {
                click: me.onCreateGroup
            },
            //fires, when the user tries to delete a group
            'site-tree button[action=onDeleteGroup]': {
                click: me.onDeleteGroup
            },
            //fires everytime an item in the tree is clicked eg. the selection is changed
            'site-tree': {
                itemclick: me.onItemClick
            },
            //fires, when the user confirms the new groupname and variable
            '[action=onCreateGroupSubmit]': {
                click: me.onCreateGroupSubmit
            },
            //fires when the user tries to close the createGroup window
            '[action=onCreateGroupWindowClose]': {
                click: me.onCreateGroupWindowClose
            }
        });

        me.callParent(arguments);
    },

    /**
     * Event listener method which is called when an item in the tree is clicked.
     * Depending on the type (leaf or non-leaf), it will activate the necessary buttons for user interaction
     * Further, if the item is a Site, it will generate an array from the grouping string, which is necessary for the checkboxes to work correctly
     * It will then call form.loadRecord() to display the record in the detail form.
     * An embed code for the site will be created as well.
     * If the item is not a site, it will just set button states accordingly.
     *
     * @param item
     * @param record
     */
    onItemClick: function(item,record) {

        var me = this,
            form = me.getDetailForm(),
            translation = form.translationPlugin,
            /*{if {acl_is_allowed privilege=deleteGroup}}*/
            deleteGroupButton = me.getDeleteGroupButton(),
            /*{/if}*/
            /*{if {acl_is_allowed privilege=deleteSite}}*/
            deleteSiteButton = me.getDeleteSiteButton(),
            /*{/if}*/
            saveSiteButton = me.getSaveSiteButton(),

            ddselector = form.down('ddselector'),
            groupStore = ddselector.fromStore,
            selectedStore = ddselector.toStore;

        me.getAttributeForm().setDisabled(true);

        translation.translationMerge = false;
        translation.translationKey = record.get('helperId');
        translation.initConfig(form);

        //determine if the item is a group or a site
        if (record.data.parentId != 'root' || record.isLeaf()) {

            //set button states
            /*{if {acl_is_allowed privilege=deleteGroup}}*/
            deleteGroupButton.disable();
            /*{/if}*/
            /*{if {acl_is_allowed privilege=deleteSite}}*/
            deleteSiteButton.enable();
            /*{/if}*/
            /*{if {acl_is_allowed privilege=updateSite}}*/
            form.saveButton.enable();
            /*{else}*/
            form.saveButton.disable();
            /*{/if}*/

            groupStore.load({
                params: {
                    grouping: record.data.grouping
                }
            });
            selectedStore.load({
                params: {
                    grouping: record.data.grouping
                }
            });
            //load record into the form
            //hotfix find a better solution for this after beta
            //record.data.description = record.data.description.split("(")[0];
            form.loadRecord(record);

            me.getAttributeForm().loadAttribute(record.get('helperId'));

            //build and set the embed code
            //the preceding '<' is necessary to display the string without interference from the script renderer
            var embedCode = '<' + 'a href="{literal}{url controller=custom sCustom={/literal}' + record.data.helperId + '}" title="' + record.data.description + '">' + record.data.description +'</a>';
            form.down('textfield[name=embedCode]').setValue(embedCode);

        } else {
            //set button states
            /*{if {acl_is_allowed privilege=deleteGroup}}*/
            if (record.data.key === 'disabled') {
                deleteGroupButton.disable();
            } else {
                deleteGroupButton.enable();
            }
            /*{/if}*/
            /*{if {acl_is_allowed privilege=deleteSite}}*/
            deleteSiteButton.disable();
            /*{/if}*/
        }

    },

    /**
     * Event listener method which is called when the user tries to create a new group
     * This will open the createGroupWindow where User interaction is handled.
     */
    onCreateGroup: function() {
        var me = this;
        me.createGroupWindow().show();
    },

    /**
     * Event listener method which will be called when the onDeleteGroup event was fired.
     * A confirmation dialog will open, which, in turn, will send an ajax request containing the group name to the site php controller that handles deletion.
     */
    onDeleteGroup: function() {
        var me = this,
            tree = me.getNavigationTree(),
            selection = tree.getSelectionModel().getSelection()[0],
            groupName = selection.data.text,
            templateVariable = selection.data.id;

        Ext.Msg.confirm('{s name=onDeleteGroupConfirmationBoxCaption}Delete this group?{/s}', Ext.String.format('{s name=onDeleteGroupConfirmationBoxText}Are you sure you want to delete the group \'[0]\'?{/s}', groupName), function(btn){
            if (btn == 'yes'){
                Ext.Ajax.request({
                    url : '{url action=deleteGroup}',
                    scope:this,
                    params: {
                        templateVar: templateVariable
                    },
                    callback: function() {
                        //reload the stores and display a success message
                        me.getStore('Nodes').load();
                        me.getStore('Groups').load();
                    },
                    success: function(){
                        Shopware.Notification.createGrowlMessage('','{s name=onDeleteGroupSuccess}The group has been deleted successfully.{/s}', '{s name=mainWindowTitle}{/s}');
                    },
                    failure: function(response) {
                        //display an error message, followed by the actual error text
                        var responseObject = Ext.decode(response.responseText),
                            errorMsg = responseObject.message;
                        Shopware.Notification.createGrowlMessage('','{s name=onDeleteGroupError}An error has occurred while trying to delete the group: {/s}' + errorMsg, '{s name=mainWindowTitle}{/s}');
                    }
                });
            }
        });
    },

    /**
     * Event listener method which will be called when the createGroupWindow event was fired.
     * This creates the createGroup dialog window.
     */
    createGroupWindow: function() {
        var me = this;

        return me.getView('site.GroupDialog').create({});
    },

    /**
     * Event listener method which will be called when the onCreateGroupSubmit event was fired.
     * A confirmation dialog will open, which will in turn send an ajax request containing the group name and template variable to the site php controller.
     * It will also display an error message if either the groupName or the templateVariable is already being used
     * @param btn
     */
    onCreateGroupSubmit: function(btn) {
        var me = this,
            dialogWindow = btn.up('window'),
            groupName = dialogWindow.down('textfield[name=description]').getValue(),
            templateVar = dialogWindow.down('textfield[name=templateVar]').getValue();
            //send ajax request containing groupName and templateVariable
            Ext.Ajax.request({
                url : '{url action=createGroup}',
                scope: me,
                params: {
                    groupName: groupName,
                    templateVar: templateVar
                },
                success: function(response){
                    //get the response object
                    var responseObject = Ext.decode(response.responseText);

                    if (responseObject.success) {
                        //destroy the window, reload the stores
                        dialogWindow.destroy();
                        me.getStore('Nodes').load();
                        me.getStore('Groups').load();

                        //display a success message
                        Shopware.Notification.createGrowlMessage('','{s name=onCreateGroupSuccess}The group has been created successfully.{/s}', '{s name=mainWindowTitle}{/s}');
                    } else {
                        if (responseObject.message == 'nameExists') {
                            dialogWindow.destroy();
                            Shopware.Notification.createGrowlMessage('',Ext.String.format('{s name=onCreateGroupGroupNameExisting}The group \'[0]\' already exists.{/s}', groupName));
                            return;
                        }
                        if (responseObject.message == 'variableExists') {
                            dialogWindow.destroy();
                            Shopware.Notification.createGrowlMessage('',Ext.String.format('{s name=onCreateGroupTemplateVariableExisting}The template variable \'[0]\' is already in use.{/s}', templateVar));
                            return;
                        }
                        dialogWindow.destroy();
                        Shopware.Notification.createGrowlMessage('','{s name=onCreateGroupError}An error has occurred while trying to create the group: {/s}' + responseObject.message, '{s name=mainWindowTitle}{/s}');
                    }
                },
                failure: function(response) {
                    //get the response object
                    var responseObject = Ext.decode(response.responseText),
                        errorMsg = responseObject.message;

                    //display an error message followed by the actual error
                    Shopware.Notification.createGrowlMessage('','{s name=onCreateGroupError}An error has occurred while trying to create the group: {/s}' + errorMsg, '{s name=mainWindowTitle}{/s}');
                }
            });
    },

    /**
     * Event listener method which will be called when the onCreateGroupWindowClose event was fired.
     * This will just close the window.
     * @param btn
     */
    onCreateGroupWindowClose: function(btn) {
        var dialogWindow = btn.up('window');
        dialogWindow.destroy();
    }
});
//{/block}
