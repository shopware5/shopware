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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/user_manager/view/main}

/**
 * Shopware Backend - View for role-grid
 *
 * todo@all: Documentation
 */
//{block name="backend/user_manager/view/roles/list"}
Ext.define('Shopware.apps.UserManager.view.roles.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.usermanager-roles-list',
    region: 'center',
    autoScroll: true,
    height: '100%',
    selType: 'checkboxmodel',

    /**
     * Initialize the view components
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.dockedItems = me.createDockedToolBar();
        me.plugins = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            listeners: {
                canceledit: function (editor, e) {
                    if (!Ext.isDefined(e.record.get('id'))) {
                        e.store.remove(e.record);
                    }
                },
                beforeedit: function (editor, e) {
                    var fields = me.getFieldsToLockForAdmin(),
                        form = editor.getEditor().form;

                    if (e.record.get('name') === 'local_admins') {
                        Ext.each(fields, function (field) {
                            form.findField(field).disable();
                        });
                        return;
                    }

                    Ext.each(fields, function(field) {
                        form.findField(field).enable();
                    });
                }
            }
        });
        me.rowEditing = me.plugins;

        me.on('edit', me.onEditRow, me);

        me.on('activate', function() {
            me.getStore().load();
        });

        // Define the columns and renderers
        me.columns = [{
            header: '{s name=roleslist/colname}{/s}',
            dataIndex: 'name',
            flex: 1,
            editor: {
                xtype: 'textfield',
                emptyText: '{s name=roles_list/enterName}{/s}'
            }
        }, {
            header: '{s name=roleslist/coldescription}{/s}',
            dataIndex: 'description',
            flex: 1,
            editor: {
                xtype: 'textfield',
                emptyText: '{s name=roles_list/enterDescription}{/s}'
            }
        }, {
            header: '{s name=roleslist/colsource}{/s}',
            dataIndex: 'source',
            flex: 1
        }, {
            xtype: 'booleancolumn',
            header: '{s name=roleslist/colactive}{/s}',
            dataIndex: 'enabled',
            flex: 1,
            editor: {
                xtype: 'checkbox',
                inputValue: 'true',
                uncheckedValue: 'false'
            }
        }, {
            xtype: 'booleancolumn',
            header: '{s name=roleslist/coladmin}{/s}',
            dataIndex: 'admin',
            flex: 1,
            editor: {
                xtype: 'checkbox',
                inputValue: 'true',
                uncheckedValue: 'false'
            }
        }
        /* {if {acl_is_allowed privilege=delete}} */
        ,{
            xtype: 'actioncolumn',
            width: 50,
            items: [{
                iconCls: 'sprite-minus-circle',
                cls: 'delete',
                tooltip: '{s name=roleslist/colactiondelete}{/s}',
                handler:function (view, rowIndex, colIndex, item) {
                    me.fireEvent('deleteRole', view, rowIndex, colIndex, item);
                },
                getClass: function(value, metaData, record) {
                    if (record.get('name') === 'local_admins') {
                        return 'x-hidden';
                    }
                }
            }]
        }
        /* {/if} */];


        // Toolbar
        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: [
        /* {if {acl_is_allowed privilege=create}} */
            {
                iconCls: 'sprite-plus-circle',
                text: '{s name=roleslist/addrole}{/s}',
                action: 'addRole'
            }
        /* {/if} */
            /* {if {acl_is_allowed privilege=delete}} */
            ,{
                iconCls: 'sprite-minus-circle',
                text: '{s name=roleslist/deleterole}{/s}',
                disabled: true,
                action: 'deleteRole'
            }
            /* {/if} */
            ]
        });


        me.dockedItems = Ext.clone(me.dockedItems);
        me.dockedItems.push(me.toolbar);

        me.callParent(arguments);
    },

    createDockedToolBar: function () {
        var me = this;

        return [{
            dock: 'bottom',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        }];
    },

    getFieldsToLockForAdmin: function() {
        return ['name', 'admin', 'enabled'];
    },

    /**
     * Event listener method which will be fired when the user
     * edits a row in the role grid with the built-in row
     * editor.
     *
     * Saves the edited record to the store.
     *
     * @event edit
     * @param { object } editor
     * @param { object } event
     * @return void
     */
    onEditRow: function(editor, event) {
        var store = event.store;

        Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {
            editor.grid.setLoading(true);
            store.sync({
                callback: function () {
                    editor.grid.setLoading(false);
                }
            });
            Shopware.Notification.createGrowlMessage(
                '{s name=user/Success}{/s}',
                '{s name=roles_list/updatedSuccesfully}{/s}',
                '{s name="user/userManager"}{/s}'
            );
        }, function() {
            event.record.reject();
        });
    }
});
//{/block}
