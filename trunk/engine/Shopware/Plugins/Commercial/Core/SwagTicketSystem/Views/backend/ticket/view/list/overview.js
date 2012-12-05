/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Ticket
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/ticket/main}
//{block name="backend/ticket/view/list/overview"}
Ext.define('Shopware.apps.Ticket.view.list.Overview', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.grid.Panel',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-list-overview',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-list-overview',

    border: 0,
    bodyBorder: 0,

    /**
     * Initialize the component
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.addEvents('addTicket', 'deleteTickets', 'changeStatus', 'changeEmployee', 'searchTicket', 'selectionChange', 'openCustomer', 'editTicket', 'deleteTicket');

        me.store = me.overviewStore;
        me.tbar = me.createActionToolbar();
        me.bbar = me.createPagingToolbar();
        me.selModel = me.createSelModel();
        me.columns = me.createColumns();
        me.plugins = me.createPlugins();

        me.callParent(arguments);
    },

    createPlugins: function() {
        var me = this,
            rowEditor = Ext.create('Ext.grid.plugin.RowEditing', {
                clicksToEdit: 2,
                autoCancel: true,
                listeners: {
                    scope: me,
                    edit: function(editor, e) {
                        me.fireEvent('saveOrder', editor, e, me.listStore)
                    }
                }
            });
        return [ rowEditor ];
    },


    /**
     * Creates the column model for the component.
     *
     * @public
     * @return Array computed columns
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'id',
            header: '#',
            width: 60,
            renderer: me.gridColorRenderer
        }, {
            xtype: 'datecolumn',
            dataIndex: 'receipt',
            header: '{s name=overview/columns/receipt}Created{/s}',
            flex: 1
        }, {
            xtype: 'datecolumn',
            dataIndex: 'lastContact',
            header: '{s name=overview/columns/last_contact}Last contact{/s}',
            flex: 1
        }, {
            dataIndex: 'ticketTypeName',
            header: '{s name=overview/columns/ticket_type}Type{/s}',
            flex: 1
        }, {
            dataIndex: 'isoCode',
            header: '{s name=overview/columns/iso}ISO{/s}',
            width: 50
        }, {
            dataIndex: 'statusId',
            header: '{s name=overview/columns/status}Status{/s}',
            flex: 1,
            renderer: me.statusRenderer,
            editor: {
                xtype: 'combobox',
                store: me.statusStore,
                valueField: 'id',
                displayField: 'description'
            }
        }, {
            dataIndex: 'contact',
            header: '{s name=overview/columns/customer}Customer{/s}',
            flex: 1,
            renderer: me.emailRenderer
        }, {
            dataIndex: 'company',
            header: '{s name=overview/columns/company}Company{/s}',
            flex: 1
        }, {
            dataIndex: 'employeeId',
            header: '{s name=overview/columns/employee}Employee{/s}',
            flex: 1,
            renderer: me.employeeRenderer,
            editor: {
                xtype: 'combobox',
                store: Ext.create('Shopware.store.User').load(),
                valueField: 'id',
                displayField: 'name'
            }
        }, {
            xtype: 'actioncolumn',
            header: '{s name=overview/columns/actions}Action(s){/s}',
            width: 80,
            items: me.getActionColumnItems()
        }];
    },

    /**
     * Returns the action toolbar which
     * is located above the grid.
     *
     * @public
     * @return [object] Ext.toolbar.Toolbar
     */
    createActionToolbar: function() {
        var me = this;

        me.deleteButton = Ext.create('Ext.button.Button', {
            text: '{s name=toolbar/delete_marked}Delete marked{/s}',
            iconCls: 'sprite-minus-circle',
            disabled: true,
            handler: function(btn) {
                Ext.MessageBox.confirm('{s name=window_title}Ticket system{/s}', '{s name=overview/button/delete_confirm}Are you sure to delete the selected ticket(s) in the list?{/s}', function(button) {
                    if(button != 'yes') {
                        return false;
                    }
                    me.fireEvent('deleteTickets', btn, me);
                });
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
            /*{if {acl_is_allowed privilege=create}}*/
            {
                xtype: 'button',
                iconCls: 'sprite-plus-circle',
                text: '{s name=toolbar/add}Create new ticket{/s}',
                handler: function(btn) {
                    me.fireEvent('addTicket', btn, me);
                }
            },
            /*{/if}*/

            /*{if {acl_is_allowed privilege=delete}}*/
            me.deleteButton,
            /*{/if}*/
            {
                xtype: 'tbseparator'
            }, {
                xtype: 'combobox',
                fieldLabel: '{s name=toolbar/status}Status{/s}',
                labelWidth: 50,
                allowBlank: true,
                valueField: 'id',
                displayField: 'description',
                emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
                store: me.statusComboStore.load(),
                listeners: {
                    scope: me,
                    change: function(field, newValue, oldValue) {
                        me.fireEvent('changeStatus', field, newValue, oldValue, me);
                    }
                }
            }, {
                xtype: 'combobox',
                fieldLabel: '{s name=toolbar/employee}Employee{/s}',
                labelWidth: 70,
                allowBlank: true,
                valueField: 'id',
                displayField: 'name',
                emptyText: '{s name=toolbar/combo_empty}Please select...{/s}',
                store: me.employeeStore,
                listeners: {
                    scope: me,
                    change: function(field, newValue, oldValue) {
                        me.fireEvent('changeEmployee', field, newValue, oldValue, me);
                    }
                }
            }, '->', {
                xtype: 'textfield',
                name: 'search',
                cls: 'searchfield',
                emptyText: '{s name=toolbar/search/empty_text}Search...{/s}',
                listeners: {
                    scope: me,
                    buffer: 500,
                    change: function(field, newValue, oldValue) {
                        me.fireEvent('searchTicket', field, newValue, oldValue, me);
                    }
                }
            }, ' ']
        });
    },

    /**
     * Creates the items of the action column
     *
     * @return Array action column items
     */
    getActionColumnItems: function() {
        var me = this,
                actionColumnData = [];

        /*{if {acl_is_allowed privilege=read}}*/
        actionColumnData.push( {
            iconCls: 'sprite-user--arrow',
            tooltip: '{s name=overview/columns/open_customer_tip}Open customer{/s}',
            handler: function(view, rowIdx, colIdx, item, e, record) {
                me.fireEvent('openCustomer', view, record, rowIdx, colIdx, item, e);
            }
        });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=update}}*/
        actionColumnData.push({
            iconCls: 'sprite-pencil',
            tooltip: '{s name=overview/columns/edit_tip}Edit ticket{/s}',
            handler: function(view, rowIdx, colIdx, item, e, record) {
                me.fireEvent('editTicket', view, record, rowIdx, colIdx, item, e);
            }
        });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=delete}}*/
        actionColumnData.push({
            iconCls: 'sprite-minus-circle',
            tooltip: '{s name=overview/columns/delete_tip}Delete ticket{/s}',
            handler: function(view, rowIdx, colIdx, item, e, record) {
                Ext.MessageBox.confirm('{s name=window_title}Ticket system{/s}', '{s name=overview/button/delete_confirm}Are you sure to delete the selected ticket(s) in the list?{/s}', function(button) {
                    if(button != 'yes') {
                        return false;
                    }
                    me.fireEvent('deleteTicket', view, record, rowIdx, colIdx, item, e);
                });
            }
        });
        /*{/if}*/
        return actionColumnData;
    },

    /**
     * Returns the paging toolbar which
     * is located under the grid.
     *
     * @public
     * @return [object] Ext.toolbar.Paging
     */
    createPagingToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            displayInfo:true
        });
    },

    /**
     * Creates the selection model which is used in this grid.
     *
     * @public
     * @return [object] Ext.selection.CheckboxModel
     */
    createSelModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    me.fireEvent('selectionChange', selections);
                }
            }
        });
    },

    /**
     * Renderer which wraps the customer name
     * in a hyperlink. The hyperlink is set to
     * the eMail address of the customer.
     *
     * @public
     * @param [string] value - Value of the column
     * @param [object] meta - Meta object of the table row
     * @param [object] record - Shopware.apps.Ticket.model.List
     * @return [string] formatted customer name
     */
    emailRenderer: function(value, meta, record) {
        var email = record.get('email');

        if(!email.length) {
            return value;
        }
        if(!value) {
            return '<a href="mailto:' + email + '">' + email + '<a/>';
        }
        return '<a href="mailto:' + email + '">' + value + '<a/>';
    },


    /**
     * Renderer which requests the status name for the status
     * store to render the full description of the status.
     *
     * @public
     * @param [string] value - Value of the column
     * @param [object] meta - Meta object of the table row
     * @param [object] record - Shopware.apps.Ticket.model.List
     * @return [string] status description
     */
    statusRenderer: function(value, meta, record) {
        var me = this;
        if(value === 0) {
            return value;
        }

        var store = me.statusStore,
            status = store.getById(~~(1 * value));

        if(!status) {
            return value;
        }
        return status.get('description');
    },
    
    /**
     * Renderer which requests the employee name for the employee
     * store to render the full name of the employee.
     *
     * @public
     * @param [string] value - Value of the column
     * @param [object] meta - Meta object of the table row
     * @param [object] record - Shopware.apps.Ticket.model.List
     * @return [string] employee name
     */
    employeeRenderer: function(value, meta, record) {
        var me = this;

        var store = me.employeeStore,
            employee = store.getById(~~(1 * value));

        if(!employee) {
            return value;
        }
        return employee.get('name');
    },

    /**
     * Renders a div box if the specific grid color.
     *
     * @param [string] value - Value of the column
     * @param [object] meta - Meta object of the table row
     * @param [object] record - Shopware.apps.Ticket.model.List
     * @return [string] formatted color + the id of the ticket
     */
    gridColorRenderer: function(value, meta, record) {
        var color = record.get('ticketTypeColor');
        if(!color) {
            return '#' + value;
        }

        return '<div style="width:14px;height:14px;background-color: '+ color +';display:inline-block;vertical-align: middle"></div>&nbsp;#' + value;
    }
});
//{/block}
