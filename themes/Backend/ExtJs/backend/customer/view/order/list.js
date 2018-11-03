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
 * @package    Customer
 * @subpackage Order
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/order}

/**
 * Shopware UI - Customer list backend module
 *
 * todo@all: Documentation
 */
// {block name="backend/customer/view/order/list"}
Ext.define('Shopware.apps.Customer.view.order.List', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.grid.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.customer-order-grid',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'order-list',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        search: '{s name=toolbar/search_empty_text}Search...{/s}',
        openOrder: '{s name=column/open_order}Open order in a new window{/s}',
        orderTime: '{s name=column/time}Order date{/s}',
        number: '{s name=column/number}Order number{/s}',
        paymentMethod: '{s name=column/payment_method}Payment method{/s}',
        dispatchMethod: '{s name=column/dispatch_method}Shipping type{/s}',
        orderStatus: '{s name=column/order_status}Order status{/s}',
        paymentStatus: '{s name=column/payment_status}Payment status{/s}',
        total: '{s name=column/total}Total{/s}'
    },

    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.store = me.gridStore;
        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();
        me.dockedItems = [ me.toolbar, me.pagingbar ];
        me.callParent(arguments);
    },

    /**
     * Registers the "openOrder" event which is fired when
     * the user clicks on the action column of the grid
     * to open the order in a new window.
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the grid action
             * column to open the order in a new window
             *
             * @event generatePassword
             * @param [object] record - Associated store record
             */
            'openOrder',

            /**
             * Event will be fired when the user insert a search string into the text field
             *
             * @event generatePassword
             * @param [string] value - inserted value
             * @param [object] grid - grid store
             */
            'searchOrder'

        );
    },

    /**
     * Creates the paging toolbar for the customer grid to allow
     * and store paging. The paging toolbar uses the same store as the Grid
     *
     * @return [Ext.toolbar.Paging] - The paging toolbar for the customer grid
     */
    getPagingBar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.gridStore,
            dock: 'bottom',
            displayInfo: true
        });
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [
            {
                header: me.snippets.orderTime,
                dataIndex: 'orderTime',
                flex: 1,
                renderer: me.orderDateColumn
            },
            {
                header: me.snippets.number,
                dataIndex: 'orderNumber',
                flex: 1
            },
            {
                header: me.snippets.paymentMethod,
                dataIndex: 'paymentId',
                flex: 1,
                renderer: me.paymentColumn
            },
            {
                header: me.snippets.dispatchMethod,
                dataIndex: 'dispatchId',
                flex: 1,
                renderer: me.dispatchColumn
            },
            {
                header: me.snippets.orderStatus,
                dataIndex: 'orderStatusId',
                flex: 1,
                renderer: me.orderStatusColumn
            },
            {
                header: me.snippets.paymentStatus,
                dataIndex: 'paymentStatusId',
                flex: 1,
                renderer: me.paymentStatusColumn
            },
            {
                header: me.snippets.total,
                dataIndex: 'invoiceAmount',
                flex: 1,
                renderer: me.invoiceAmountColumn
            },
            {
                /**
                 * Special column type which provides
                 * clickable icons in each row
                 */
                xtype: 'actioncolumn',
                width: 50,
                items: [
                    {
                        cls: 'editBtn',
                        iconCls: 'sprite-sticky-notes-pin',
                        action: 'customer-order-list-open-order',
                        tooltip: me.snippets.openOrder,
                        /**
                         * Add button handler to fire the generatePassword event which is handled
                         * in the detail controller. The detail controller generates a password and set it into the password field
                         */
                        handler: function (view, rowIndex, colIndex, item) {
                            var store = view.getStore(),
                                record = store.getAt(rowIndex);

                            me.fireEvent('openOrder', record);
                        }
                    }
                ]
            }
        ];
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function () {
        var me = this;
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            cls: Ext.baseCSSPrefix + 'order-list-toolbar',
            items: [
                '->',
                {
                    xtype: 'textfield',
                    name: 'searchfield',
                    cls: 'searchfield',
                    width: 170,
                    emptyText: me.snippets.search,
                    enableKeyEvents: true,
                    checkChangeBuffer: 500,
                    listeners: {
                        change: function(field, value) {
                            me.fireEvent('searchOrder', value, me.gridStore);
                        }
                    }
                },
                { xtype: 'tbspacer', width: 6 }
            ]
        });
    },
    /**
     * Formats the invoice amount grid column
     *
     * @param [string] value
     * @return [string]
     */
    invoiceAmountColumn: function (value) {
        return Ext.util.Format.currency(value);
    },

    /**
     * Formats the order time column
     * @param value
     */
    orderDateColumn: function (value) {
        // eslint-disable-next-line valid-typeof
        if (typeof value === Ext.undefined) {
            return value;
        }
        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
    },

    /**
     *
     */
    orderStatusColumn: function(value, metaData, record) {
        var me = this;

        if (me.orderStatusStore) {
            var orderStatus = me.orderStatusStore.getById(value);
            return orderStatus.get('description');
        } else {
            return '';
        }
    },

    /**
     *
     */
    paymentStatusColumn: function(value, metaData, record) {
        var me = this;

        if (me.paymentStatusStore) {
            var paymentStatus = me.paymentStatusStore.getById(value);
            if (paymentStatus instanceof Ext.data.Model) {
                return paymentStatus.get('description');
            } else {
                return '';
            }
        } else {
            return '';
        }
    },

    /**
     *
     */
    dispatchColumn: function(value, metaData, record) {
        var me = this;

        if (me.dispatchStore) {
            var dispatch = me.dispatchStore.getById(value);
            if (!dispatch) {
                return '';
            }
            return dispatch.get('name');
        } else {
            return '';
        }
    },

    /**
     *
     */
    paymentColumn: function(value, metaData, record) {
        var me = this;

        if (me.paymentStore) {
            var payment = me.paymentStore.getById(value);
            if (!payment) {
                return '';
            }
            return payment.get('description');
        } else {
            return '';
        }
    }

});
// {/block}
