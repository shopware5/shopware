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
 */

//{namespace name=backend/index/view/widgets}

/**
 * Shopware UI - Sales Widget
 *
 * This file holds off the sales widget.
 */
//{block name="backend/index/view/widgets/orders"}
Ext.define('Shopware.apps.Index.view.widgets.Orders', {
    extend: 'Shopware.apps.Index.view.widgets.Base',
    alias: 'widget.swag-last-orders-widget',
    title: '{s name=orders/title}Last orders{/s}',
    layout: 'fit',

    /**
     * Snippets for this widget.
     * @object
     */
    snippets: {
        headers: {
            date: '{s name=orders/headers/date}Date{/s}',
            number: '{s name=orders/headers/number}Nr.{/s}',
            customer: '{s name=orders/headers/customer}Customer{/s}',
            amount: '{s name=orders/headers/amount}Amount{/s}'
        }
    },

    ordersStore: null,

    constructor: function() {
        var me = this;

        me.ordersStore = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Index.model.Orders',
            remoteFilter: true,
            clearOnLoad: false,
            autoLoad: true,

            proxy: {
                type: 'ajax',
                url: '{url controller="widgets" action="getLastOrders"}',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });

        me.callParent(arguments);
    },

    /**
     * Initializes the widget.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            border: 0,
            store: me.ordersStore,
            xtype: 'grid',
            viewConfig: {
                hideLoadingMsg: true
            },
            columns: me.createColumns()
        }];

        me.tools = [{
            type: 'refresh',
            scope: me,
            handler: me.refreshView
        }];

        me.createTaskRunner();
        me.callParent(arguments);
    },

    /**
     * Registers a new task runner to refresh
     * the store after a given time interval.
     *
     * @public
     * @param [object] store - Ext.data.Store
     * @return void
     */
    createTaskRunner: function() {
        var me = this;

        me.storeRefreshTask = Ext.TaskManager.start({
            scope: me,
            run: me.refreshView,
            interval: 300000
        });
    },

    /**
     * Helper method which will be called by the
     * task runner and when the user clicks the
     * refresh icon in the panel header.
     *
     * @public
     * @return void
     */
    refreshView: function() {
        var me = this;
        me.ordersStore.load();
    },

    /**
     * Helper method which creates the columns
     * for the grid.
     *
     * @public
     * @return [array] generated columns
     */
    createColumns: function() {
        var me = this;

        return [{
            header: me.snippets.headers.date,
            dataIndex: 'date',
            renderer: me.dateColumn,
            flex: 1
        }, {
            header: me.snippets.headers.number,
            dataIndex: 'orderNumber',
            flex: 1
        }, {
            header: me.snippets.headers.customer,
            dataIndex: 'customer',
            flex: 1
        }, {
            header: me.snippets.headers.amount,
            dataIndex: 'amount',
            flex: 1,
            renderer: function(value) {
                return Ext.util.Format.currency(value);
            }
        }, {
            xtype: 'actioncolumn',
            width: 70,
            items: [{
                iconCls: 'sprite-user--pencil',
                handler: function(view, rowIndex, colIndex, item, event, record) {

                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Customer',
                        action: 'detail',
                        params: {
                            customerId: ~~(1 * record.get('customerId'))
                        }
                    });
                }
            }, {
                iconCls: 'sprite-sticky-note--pencil',
                handler: function(view, rowIndex, colIndex, item, event, record) {

                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Order',
                        action: 'detail',
                        params: {
                            orderId: ~~(1 * record.get('id'))
                        }
                    });
                }
            }]
        }];
    },

    /**
     * Formats the date column
     *
     * @param [string] - The order time value
     * @return [string] - The passed value, formatted with Ext.util.Format.date()
     */
    dateColumn:function (value, metaData, record) {
        if ( value === Ext.undefined ) {
            return value;
        }

        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
    }
});
//{/block}
