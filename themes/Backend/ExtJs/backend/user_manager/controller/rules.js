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
 * @package    UserManager
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/user_manager/view/main}

/**
 * Shopware Backend - User Manager rule controller
 *
 * The user manager rule controller handles all action around the rules view (Add and Tree view).
 */
//{block name="backend/user_manager/controller/rules"}
Ext.define('Shopware.apps.UserManager.controller.Rules', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Holder property which saves the instance of the application
     * window for later usage
     *
     * @null
     */
    appContent: null,

    refs: [
        { ref: 'rulesTree', selector: 'user-manager-rules-tree' },
        { ref: 'saveRolePrivilegesButton', selector: 'user-manager-rules-tree button[name=saveRolePrivileges]' }
    ],

    /**
     * Contains all snippets for this component
     * @object
     */
    snippets: {
        successTitle:'{s name=message/resource/delete_success_message}Successful{/s}',
        errorTitle:'{s name=message/privilege/save_error_title}Error{/s}',
        errorMessage:'{s name=message/privilege/save_error_message}An error has occurred while saving:{/s}',

        resourceDelete: {
            successMessage:'{s name=message/resource/delete_success_title}Resource has been removed{/s}'
        },
        privilegeDelete: {
            successMessage:'{s name=message/privilege/delete_success_title}Privilege has been removed{/s}'
        },
        resourceSave: {
            successMessage:'{s name=message/resource/save_success_title}Resource has been saved{/s}'
        },
        privilegeSave: {
            successMessage:'{s name=message/privilege/save_success_title}Privilege has been saved{/s}'
        },
        roleSave: {
            successMessage:'{s name=message/role/save_success_title}Role privileges have been saved{/s}'
        },

        growlMessage: '{s name=growlMessage}User Management{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;
        me.control({
            'user-manager-rules-tree': {
                deleteResource: me.onDeleteResource,
                deletePrivilege: me.onDeletePrivilege,
                addResource: me.onAddResource,
                addPrivilege: me.onAddPrivilege,
                saveRolePrivileges: me.onSaveRolePrivileges,
                roleSelect: me.onRoleSelect
            },
            'user-manager-rule-add': {
                saveResource: me.onSaveResource,
                savePrivilege: me.onSavePrivilege
            }

       });
    },



    /**
     * Event will be fired when the user want to create a new privilege.
     *
     * @param { Ext.window.Window } window - The add window
     * @param { Ext.form.Panel } form - The form panel
     * @param { Ext.data.Model } record - The new record
     * @param { Ext.data.Store } store - The rules store
     */
    onSavePrivilege: function(window, form, record, store) {
        var me = this,
            rootNode = store.getRootNode();

        if (!form.getForm().isValid()) {
            return false;
        }

        // prevent the user from creating multiple privileges with the same
        // name under a certain node
        var node = store.getNodeById(record.get('resourceId')),
            found = false;
        rootNode.eachChild(function(ch) {
          if(ch.get('id') === record.get('resourceId')){
              if(ch.findChild('name', record.get('name')) !== null) {
                  Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, '{s name=privilege/alreadyExistingMessage}A privilege with this name is already existing{/s}', me.snippets.growlMessage);
                  window.destroy();
                  found = true;
                  return false;
              }
          }
        });

        if(found === true) {
            return;
        }

        Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {
            record.save({
                callback: function (data, operation) {
                    var records = operation.getRecords(),
                            record = records[0],
                            rawData = record.getProxy().getReader().rawData;

                    if (operation.success === true) {
                        Shopware.Notification.createGrowlMessage(me.snippets.successTitle, me.snippets.privilegeSave.successMessage, me.snippets.growlMessage);
                    } else {
                        Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.errorMessage + ' ' + rawData.message, me.snippets.growlMessage);
                    }
                    window.destroy();
                    store.load();
                }
            });
        });
    },

    /**
     * Event will be fired when the user want to create a new resource.
     *
     * @param { Ext.window.Window } window - The add window
     * @param { Ext.form.Panel } form - The form panel
     * @param { Ext.data.Model } record - The new record
     * @param { Ext.data.Store } store - The rules store
     */
    onSaveResource: function(window, form, record, store) {
        var me = this,
            rootNode = store.getRootNode();

        if (!form.getForm().isValid()) {
            return false;
        }

        // Prevent the user from creating multiple resources with the same name
        if(rootNode.findChild('name', record.get('name')) !== null) {
            Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, '{s name=resource/alreadyExistingMessage}A resource with this name is already existing{/s}', me.snippets.growlMessage);
            window.destroy();
            return;
        }

        Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {
            record.save({
                callback: function (data, operation) {
                    var records = operation.getRecords(),
                            record = records[0],
                            rawData = record.getProxy().getReader().rawData;

                    if (operation.success === true) {
                        Shopware.Notification.createGrowlMessage(me.snippets.successTitle, me.snippets.resourceSave.successMessage, me.snippets.growlMessage);
                    } else {
                        Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.errorMessage + ' ' + rawData.message, me.snippets.growlMessage);
                    }
                    window.destroy();
                    store.load();
                }
            });
        });
    },

    /**
     * Event will be fired when the user change the tree checkboxes and
     * clicks the "Assign the selected privileges to the role" button
     *
     * @param { Ext.data.Store } store - The component store.
     * @param { int|null } roleId - The combo box value
     * @param { array } checkedNodes - All checked tree nodes
     */
    onSaveRolePrivileges: function(store, roleId, checkedNodes) {
        var me = this;

        if (!roleId) {
            return false;
        }
        var roleStore = Ext.create('Shopware.apps.UserManager.store.Detail');

        Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {
            roleStore.load({
                callback: function () {
                    var role = roleStore.getById(roleId),
                            privilegeStore = role['getPrivilegeStore'];

                    privilegeStore.removeAll();
                    Ext.each(checkedNodes, function (item, key) {
                        var rule = Ext.create('Shopware.apps.UserManager.model.Rules');
                        rule.set('roleId', roleId);
                        rule.set('resourceId', item.get('resourceId'));

                        if (item.get('type') === 'resource') {
                            rule.set('privilegeId', null);
                        } else {
                            rule.set('privilegeId', item.get('helperId'));
                        }
                        privilegeStore.add(rule);
                    });

                    role['getPrivilegeStore'] = privilegeStore;

                    role.save({
                        callback: function (data, operation) {
                            var records = operation.getRecords(),
                                    record = records[0],
                                    rawData = record.getProxy().getReader().rawData;

                            if (operation.success === true) {
                                Shopware.Notification.createGrowlMessage(me.snippets.successTitle, Ext.String.format(me.snippets.roleSave.successMessage, rawData.data.name), me.snippets.growlMessage);
                            } else {
                                Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.errorMessage + ' ' + rawData.message, me.snippets.growlMessage);
                            }
                            store.load();
                        }
                    });
                }
            });
        });
    },

    /**
     * Event will be fired when the user select a role in the combo box
     * which is placed on top of the rules tree
     *
     * @param { Ext.data.Store } store The component store.
     * @param { int|null } value The combo box value
     */
    onRoleSelect: function(store, value) {
        var me = this;
        if (!store || !value) {
            return true;
        }
        store.getProxy().extraParams = {
            search: store.getProxy().extraParams.search,
            role: value
        };
        store.load();
        var saveButton = me.getSaveRolePrivilegesButton();
        saveButton.setDisabled(!value);
    },

    /**
     * Event listener method which is fired when the user clicks
     * the delete action column of the rules tree component of
     * a record with the property type "resource"
     *
     * @param { Ext.data.Model } resource
     * @param { Ext.data.Store } store
     */
    onDeleteResource: function(resource, store) {
        var me = this,
            message;

        if (!resource) {
            return false;
        }
        var model = Ext.create('Shopware.apps.UserManager.model.Resource', { id: resource.get('helperId') });

        message = Ext.String.format('{s name="resource/messageDeleteResource"}Are you sure you want to delete the resource [0]?{/s}', resource.get('name'));
        Ext.MessageBox.confirm('{s name="resource/titleDeleteResource"}Delete resource{/s}', message, function (response){
            if (response !== 'yes') {
                return false;
            }

            Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {
                model.destroy({
                    callback: function (data, operation) {
                        var records = operation.getRecords(),
                                record = records[0],
                                rawData = record.getProxy().getReader().rawData;

                        if (operation.success === true) {
                            Shopware.Notification.createGrowlMessage(me.snippets.successTitle, me.snippets.resourceDelete.successMessage, me.snippets.growlMessage);
                        } else {
                            Shopware.Notification.createGrowlMessage(me.snippets.errorTitle, me.snippets.errorMessage + ' ' + rawData.message, me.snippets.growlMessage);
                        }
                        store.load();
                    }
                });
            });
        });


    },

    /**
     * Event listener method which is fired when the user clicks
     * the delete action column of the rules tree component of
     * a record with the property type "privilege"
     *
     * @param { Ext.data.Model } privilege
     * @param { Ext.data.Store } store
     */
    onDeletePrivilege: function(privilege, store) {
        var me = this,
            message;

        if (!privilege) {
            return false;
        }
        var model = Ext.create('Shopware.apps.UserManager.model.Privilege', { id: privilege.get('helperId') });

        message = Ext.String.format('{s name="privilege/messageDeletePrivilege"}Are you sure you want to delete the privilege [0]?{/s}', privilege.get('name'));
        Ext.MessageBox.confirm('{s name="privilege/titleDeletePrivilege"}Delete Privilege{/s}', message, function (response){
            if (response !== 'yes') {
                return false;
            }

            Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {
                model.destroy({
                    callback: function (data, operation) {
                        var records = operation.getRecords(),
                                record = records[0],
                                rawData = record.getProxy().getReader().rawData;

                        if (operation.success === true) {
                            Shopware.Notification.createGrowlMessage(me.snippets.privilegeDelete.successTitle, me.snippets.privilegeDelete.successMessage, me.snippets.growlMessage);
                        } else {
                            Shopware.Notification.createGrowlMessage(me.snippets.privilegeDelete.errorTitle, me.snippets.privilegeDelete.errorMessage + ' ' + rawData.message, me.snippets.growlMessage);
                        }
                        store.load();
                    }
                });
            });
        });
    },

    /**
     * Event will be fired when the user clicks on the "add resource button"
     *
     * @param { Ext.data.Store } store - The component store.
     * @param { int } resourceId
     */
    onAddPrivilege: function(store, resourceId) {
        var record = Ext.create('Shopware.apps.UserManager.model.Privilege', {
            resourceId: resourceId
        });

        Ext.create('Shopware.apps.UserManager.view.rules.Add', {
            record: record,
            ruleStore: store
        }).show();
    },


    /**
     * Event will be fired when the user clicks on the "add privilege button"
     *
     * @param { Ext.data.Store } store - The component store.
     */
    onAddResource: function(store) {
        var record = Ext.create('Shopware.apps.UserManager.model.Resource');

        Ext.create('Shopware.apps.UserManager.view.rules.Add', {
            record: record,
            ruleStore: store
        }).show();
    }
});
//{/block}
