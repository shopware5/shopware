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
 * @package    BonusSystem
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

//{namespace name=backend/bonus_system/view/main}
//{block name="backend/bonus_system/view/orders"}
Ext.define('Shopware.apps.BonusSystem.view.main.Orders', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.bonusSystem-main-orders',
    autoScroll: true,

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        search:  '{s name=orders/search}search...{/s}',
        approved: '{s name=orders/approved}approved{/s}',
        waitingForApproval: '{s name=orders/waiting_for_approval}waiting for approval{/s}',
        toolbar: {
            approveSelected: '{s name=orders/toolbar/approve_selected}Approve selected{/s}',
        },
        tooltip: {
            openCustomer: '{s name=orders/tooltip/open_customer}Open customer{/s}',
            openOrder: '{s name=orders/tooltip/open_order}Open order{/s}'
        },
        column: {
            ordernumber: '{s name=orders/column/ordernumber}Ordernumber{/s}',
            customernumber:  '{s name=orders/column/customernumber}Customernumber{/s}',
            customer:  '{s name=orders/column/customer}Customer{/s}',
            email: '{s name=orders/column/email}Email{/s}',
            address: '{s name=orders/column/address}Address{/s}',
            amount: '{s name=orders/column/amount}Amount{/s}',
            ordertime: '{s name=orders/column/ordertime}Ordertime{/s}',
            approval: '{s name=orders/column/approval}Approval{/s}',
            points: '{s name=orders/column/points}Points{/s}'
        }
    },

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.selModel    = me.getGridSelModel();
        me.columns     = me.getColumns();
        me.dockedItems = [ me.getToolbar(), me.getPagingbar() ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * Event that will be fired when the user clicks the approve button in the toolbar
             *
             * @event approveOrders
             * @param [array] records - The selected records
             */
            'approveOrders',

            /**
             * Event that will be fired when the user insert a value into the search field of the toolbar
             *
             * @event searchOrder
             * @param [string] searchOrder
             */
            'searchOrder',

            /**
             * Event will be fired when the user clicks the "open customer" action column icon
             *
             * @event openCustomer
             * @param [Ext.data.Model]
             */
            'openCustomer',

            /**
             * Event will be fired when the user clicks the "open order" action column icon
             *
             * @event openOrder
             * @param [Ext.data.Model]
             */
            'openOrder'
        );
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel: function () {
        var me = this;

        var selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: function (sm, selections) {
                     me.approveButton.setDisabled(selections.length == 0);
                }
            }
        });
        return selModel;
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this,
            actionColumItems = [];

        actionColumItems.push({
            iconCls: 'sprite-user--arrow',
            tooltip: me.snippets.tooltip.openCustomer,
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent('openCustomer', record);
            }
        });

        actionColumItems.push({
            iconCls: 'sprite-sticky-notes-pin',
            tooltip: me.snippets.tooltip.openOrder,
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent('openOrder', record);
            }
        });

        var columns = [{
            header: me.snippets.column.ordernumber,
            dataIndex: 'ordernumber',
            flex: 1
        }, {
            header: me.snippets.column.customernumber,
            dataIndex: 'customernumber',
            flex: 1
        }, {
            header: me.snippets.column.customer,
            dataIndex: 'user',
            flex: 1
        }, {
            header: me.snippets.column.email,
            dataIndex: 'email',
            flex: 1
        }, {
            header: me.snippets.column.address,
            dataIndex: 'address',
            flex: 1
        }, {
            header: me.snippets.column.amount,
            dataIndex: 'amount',
            flex: 1
        }, {
            header: me.snippets.column.ordertime,
            dataIndex: 'ordertime',
            flex: 1
        }, {
            header: me.snippets.column.approval,
            dataIndex: 'approval',
            flex: 1,
            renderer: me.renderStatus
        }, {
            header: me.snippets.column.points,
            dataIndex: 'points',
            flex: 1
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: actionColumItems.length * 26,
            items: actionColumItems
        }];

        return columns;
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        me.approveButton  = Ext.create('Ext.Button', {
            iconCls:'sprite-tick-circle',
            text: me.snippets.toolbar.approveSelected,
            disabled: true,
            handler: function() {
                var selectionModel = me.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('approveOrders', records);
                }
            }
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui : 'shopware-ui',
            items: [
                me.approveButton,
                '->', {
                    xtype : 'textfield',
                    name : 'searchfield',
                    action : 'searchForms',
                    width: 170,
                    cls: 'searchfield',
                    enableKeyEvents: true,
                    checkChangeBuffer: 500,
                    emptyText : me.snippets.search,
                    listeners: {
                        change: function(field, value) {
                            me.fireEvent('searchOrder', value);
                        }
                    }
                }, {
                    xtype: 'tbspacer',
                    width: 6
                }]
        });

        return toolbar;
    },

    /**
     * Creates pagingbar shown at the bottom of the grid
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function () {
        var pagingbar =  Ext.create('Ext.toolbar.Paging', {
            store: this.store,
            dock: 'bottom',
            displayInfo: true
        });

        return pagingbar;
    },

    /**
     * Special render for the approval column
     * @param value
     */
    renderStatus: function(value) {
        var me = this;

        var approved = me.snippets.approved;
        var waitingForApproval = me.snippets.waitingForApproval;

        if (value == 1) {
            return '<span style="color:green;">' + approved + '</span>';
        } else {
            return '<span style="color:red;">' + waitingForApproval + '</span>';
        }
    }
});
//{/block}
