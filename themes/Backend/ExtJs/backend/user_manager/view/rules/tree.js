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
 * Shopware Backend - User Manager rule tree
 *
 * The rule tree component is a listing component for all defined resources and the resource privileges.
 * The user can create and delete new resource and privileges over the toolbar buttons or the tree action columns.
 * If the user select a role in the toolbar combo box, all privileges of the selected role will be checked in the tree.
 * The user can change the role privileges over the tree checkboxes. After the user clicks the
 * "Assign the checked privileges to the selected role" all checked privileges and resources will be assigned to the role.
 */
//{block name="backend/user_manager/view/rules/tree"}
Ext.define('Shopware.apps.UserManager.view.rules.Tree', {

    /**
     * Region of the component
     */
    region: 'center',

    /**
     * Defines that the rules tree is an extension of the Ext.tree.Panel
     */
    extend: 'Ext.tree.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.user-manager-rules-tree',

    /**
     * Hides the root node
     * @boolean
     */
    rootVisible: false,
    /**
     * Contains the component snippets
     * @object
     */
    snippets: {
        columns: {
            name: '{s name=rules/column/name}Resource / Privilege{/s}',
            action: '{s name=rules/column/action_header}Action{/s}',
            delete: '{s name=rules/column/action_delete}Delete item{/s}'
        },
        role: {
            label: '{s name=rules/role_label}Role{/s}',
            empty: '{s name=rules/role_empty}Select role to proceed{/s}'
        },
        addResource: '{s name=rules/add_resource}Add resource{/s}',
        addPrivilege: '{s name=rules/add_privilege}Add privilege{/s}',
        saveRole: '{s name=rules/save_role}Save{/s}',
        search: '{s name=rules/search}Search...{/s}',
        notSelectedTitle: '{s name=rules/not_selected_title}Error{/s}',
        notSelectedMessage: '{s name=rules/not_selected_message}No resource selected!{/s}',

        growlMessage: '{s name=growlMessage}User Management{/s}'
    },

    viewConfig: {
        animate: false
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first, with each initComponent method up the hierarchy to
     * Ext.Component being called thereafter. This makes it easy to implement and, if needed,
     * override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Set column model, store, selection model and the toolbar
        me.columns = me.createColumns();
        me.selModel = me.createSelectionModel();
        me.tbar = me.createToolbar();
        me.registerEvents();
        me.store = Ext.create('Shopware.apps.UserManager.store.Rules');
        me.store.getProxy().extraParams = {};

        me.on('activate', function() {
            me.getStore().load();
        });

        me.on('checkchange', function (node, checked) {
            me.suspendLayouts();
            if (checked) {
                if(!Ext.isEmpty(node.get('requirements'))) {
                    me.checkRequiredNodes(node, true);
                }

                Ext.each(node.childNodes, function(childNode) {
                    if(!Ext.isEmpty(childNode.get('requirements'))) {
                        me.checkRequiredNodes(childNode, true);
                    }
                    childNode.set('checked', true);
                });
                node.expand();
            } else if (node.isLeaf()) {
                node.parentNode.set('checked', false);
            } else {
                Ext.each(node.childNodes, function(childNode) {
                    childNode.set('checked', false);
                });
            }
            me.resumeLayouts(true);
        });

        me.callParent(arguments);
    },

    checkRequiredNodes: function(node, check) {
        var me = this;

        Ext.each(node.get('requirements'), function(nodeId) {
            me.getStore().getRootNode().eachChild(function(element) {
                element.eachChild(function (child) {
                    if(child.data.helperId === nodeId) {
                        if (child && child.get('checked') !== check) {
                            child.set('checked', true);
                            child.parentNode.expand();
                            if (!Ext.isEmpty(child.get('requirements'))) {
                                me.checkRequiredNodes(child, true);
                            }
                        }
                    }
                });
            });
        });
    },

    /**
     * Creates the tree selection model.
     * @return Ext.selection.RowModel
     */
    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.RowModel', {
            listeners: {
                scope: me,
                select: me.onNodeSelect
            }
        });
    },

    /**
     * Fired when a row is focused
     * @param [Ext.selection.RowModel] selModel - The tree selection model
     * @param [Ext.data.Model]
     * @return void
     */
    onNodeSelect: function(selModel, record) {
        var me = this;

        me.addPrivilegeButton.setDisabled(record.get('type') !== 'resource');
    },

    /**
     * Registers the custom component events.
     * @return void
     */
    registerEvents: function() {

        this.addEvents(
            /**
             * Event will be fired when the user clicks the delete action column
             * of a resource tree node.
             *
             * @event
             * @param [Ext.data.Model] - The select node record
             */
            'deleteResource',

            /**
             * Event will be fired when the user clicks the delete action column
             * of a resource tree node.
             *
             * @event
             * @param [Ext.data.Model] - The select node record
             */
            'deletePrivilege',

            /**
             * Event will be fired when the user insert a value into the search text field which
             * is displayed on top of the rules tree.
             *
             * @event
             * @param [Ext.String] - The search value which inserted in the search text field.
             * @param [Ext.data.Store] - The component store.
             */
            'searchResource',

            /**
             * Event will be fired when the user clicks on the "add resource button"
             *
             * @event
             * @param [Ext.data.Store] - The component store.
             */
            'addResource',

            /**
             * Event will be fired when the user clicks on the "add privilege button"
             *
             * @event
             * @param [Ext.data.Store] - The component store.
             */
            'addPrivilege',

            /**
             * Event will be fired when the user select a role in the combo box
             * which is placed on top of the rules tree
             *
             * @event
             * @param [Ext.data.Store] - The component store.
             * @param [int|null] - The combo box value
             */
            'roleSelect',

            /**
             * Event will be fired when the user change the tree checkboxes and
             * clicks the "Assign the selected privileges to the role" button
             *
             * @event
             * @param [Ext.data.Store] - The component store.
             * @param [int|null] - The combo box value
             * @param [array] - All checked nodes
             *
             */
            'saveRolePrivileges'
        );
    },

    /**
     * Creates the tree toolbar which contains the
     * add resource / privilege button and the search text field.
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function() {
        var me = this;

        me.roleStore = Ext.create('Shopware.apps.UserManager.store.Roles', {
            pageSize: 5
        });

        me.roleCombo = Ext.create('Shopware.form.field.PagingComboBox', {
            pageSize: 5,
            queryMode: 'remote',
            store: me.roleStore,
            valueField: 'id',
            displayField: 'name',
            forceSelection: true,
            allowBlank:false,
            labelWidth: 50,
            emptyText: me.snippets.role.empty,
            fieldLabel: me.snippets.role.label,
            listeners: {
                change: function(field, value) {
                    me.fireEvent('roleSelect', me.store, value);
                }
            }
        });

        /**
         * The save role button assign the selected privileges
         * to the selected role.
         * @type Ext.button.Button
         */
        /* {if {acl_is_allowed privilege=update}} */
        me.saveRoleButton = Ext.create('Ext.button.Button', {
            text: me.snippets.saveRole,
            disabled:true,
            name: 'saveRolePrivileges',
            iconCls:'sprite-disk',
            handler: function() {
                me.fireEvent('saveRolePrivileges', me.store, me.roleCombo.getValue(), me.getChecked());
            }
        });
        /* {/if} */

        /**
         * The add resource button creates a new resource
         * @type Ext.button.Button
         */
        /* {if {acl_is_allowed privilege=create}} */
        me.addResourceButton = Ext.create('Ext.button.Button', {
            text: me.snippets.addResource,
            iconCls:'sprite-plus-circle-frame',
            handler: function() {
                me.fireEvent('addResource', me.store);
            }
        });
        /* {/if} */

        /**
         * The add privilege button creates a new privilege for
         * the selected resource.
         * @type Ext.button.Button
         */
        /* {if {acl_is_allowed privilege=create}} */
        me.addPrivilegeButton = Ext.create('Ext.button.Button', {
            text: me.snippets.addPrivilege,
            iconCls:'sprite-plus-circle-frame',
            disabled: true,
            handler: function() {
                var selected = me.selModel.selected;

                if (selected.first()) {
                    me.fireEvent('addPrivilege', me.store, selected.first().get('resourceId'));
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.notSelectedTitle, me.snippets.notSelectedMessage, me.snippets.growlMessage);
                }

            }
        });
        /* {/if} */

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',
            items: [
                me.roleCombo,
        /* {if {acl_is_allowed privilege=update}} */
                { xtype:'tbspacer', width:6 },
                me.saveRoleButton,
        /* {/if} */
        /* {if {acl_is_allowed privilege=create}} */
                me.addResourceButton,
                me.addPrivilegeButton,
        /* {/if} */
            ]
        });
    },

    /**
     * Creates the column model for the TreePanel
     *
     * @return [array] columns - generated columns
     */
    createColumns: function() {
        var me = this;

        return [{
            xtype: 'treecolumn',
            text: me.snippets.columns.name ,
            flex: 1,
            sortable: true,
            dataIndex: 'name'
        },
        /* {if {acl_is_allowed privilege=delete}} */
        {
            xtype: 'actioncolumn',
            width: 50,
            text: me.snippets.columns.action,
            items: [{
                iconCls:'sprite-minus-circle-frame',
                action:'deleteNode',
                tooltip: '{s name=rules/column/action_delete}{/s}',
                /**
                 * Remove button handler to fire the deletePrivilege or deleteResource event which is handled
                 * in the rules controller.
                 */
                handler:function (view, rowIndex, colIndex, item, opts, record) {
                    if (record.get('type') === 'privilege') {
                        me.fireEvent('deletePrivilege', record, me.store);
                    } else {
                        me.fireEvent('deleteResource', record, me.store);
                    }
                }
            }]
        }
        /* {/if} */];
    }


});
//{/block}
