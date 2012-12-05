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
 * @package    NewsletterManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/swag_newsletter/main"}

/**
 * Shopware UI - Orders
 * Show orders related to newsletters
 */
//{block name="backend/newsletter_manager/view/tabs/orders"}
Ext.define('Shopware.apps.NewsletterManager.view.tabs.Orders', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.newsletter-manager-tabs-orders',
    title: '{s name=orders}Orders{/s}',

    snippets: {
        columns: {
            customer: '{s name=customer}Customer{/s}',
            orderTime: '{s name=orderTime}Order time{/s}',
            orderStatus: '{s name=orderStatus}Order status{/s}',
            paymentStatus: '{s name=paymentStatus}Payment status{/s}',
            invoiceAmount: '{s name=invoiceAmount}Invoice amount{/s}'
        }
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.columns = me.getColumns();
        me.tbar = me.getToolbar();
        me.bbar = me.getPagingbar();

        me.features = [ me.createGroupingFeature() ];

        me.addEvents(
            /**
             * Fired when the user types into the search field
             */
            'searchOrders',

            /**
             * Fired when the user clicks the 'show customer' action button
             */
            'showCustomer',

            /**
             * Fired when the user clicks the 'show order' action button
             */
             'showOrder'
        );

        me.callParent(arguments);
    },

    /**
     * create the grouping feature for the grid
     * @return Ext.grid.feature.GroupingSummary
     */
    createGroupingFeature: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.GroupingSummary', {
            groupHeaderTpl: Ext.create('Ext.XTemplate',
                '<span>{ name:this.formatHeader }</span>',
                {
                    formatHeader: function(field) {
                        return field;
                    }
                }
            )
        });
    },

    /**
     * Creates the grid columns
     * Data indices where chosen in order to match the database scheme for sorting in the PHP backend.
     * Therefore each Column requieres its own renderer in order to display the correct value.
     *
     * @return Array grid columns
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.customer,
                dataIndex: 'customer',
                flex: 1
            },
            {
                header: me.snippets.columns.orderTime,
                dataIndex: 'orderTime',
                renderer: function(value, metaData, record) {
                    return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
                },
                flex: 1
            },
            {
                header: me.snippets.columns.orderStatus,
                sortable: false,
                dataIndex: 'status',
                renderer: function(value, metaData, record) {
                    var store = me.orderStatusStore,
                        value = store.getById(value);

                    if(value instanceof Ext.data.Model ) {
                        return value.get('description');
                    }

                    return value;
                },
                flex: 1
            },
            {
                header: me.snippets.columns.paymentStatus,
                sortable: false,
                dataIndex: 'cleared',
                renderer: function(value, metaData, record) {
                    var store = me.paymentStatusStore,
                        value = store.getById(value);

                    if(value instanceof Ext.data.Model ) {
                        return value.get('description');
                    }

                    return value;
                },
                flex: 1
            },
            {
                header: me.snippets.columns.invoiceAmount,
                dataIndex: 'invoiceAmountEuro',
                align: 'right',
                renderer: function(value, metaData, record) {
                    if(Ext.isNumber(value) || Ext.isString(value)) {
                        return Ext.util.Format.currency(value);
                    }

                    return value
                },
                summaryType: function(records){
                    var i = 0,
                        length = records.length,
                        total = 0,
                        record;
                    for (; i < length; i++){
                        record = records[i];
                        total += record.get('invoiceAmountEuro');
                    }
                    return total;
                },
                summaryRenderer: function(value, summaryData, dataIndex) {
                    if(Ext.isNumber(value) || Ext.isString(value)) {
                        return '<b>' + Ext.util.Format.currency(value)+ '</b>';
                    }

                    return value
                },
                flex: 1
            },
            {
                header: '',
                align: 'right',
                xtype : 'actioncolumn',
                items : me.getActionColumn(),
                summaryType: 'count',
                summaryRenderer: function(value, summaryData, dataIndex) {
                    if(value == 1) {
                        return "<b>" + value + ' {s name=oneOrder}Order{/s}</b>';
                    }
                    return "<b>" + value + ' {s name=multiOrder}Orders{/s}</b>';
                }
            }
        ];
    },

    /**
     * Returns an array of icons for the action column
     *
     * @return Array of buttons
     */
    getActionColumn : function() {
        var me = this;

        return [
            {
                iconCls:'sprite-user--plus',
                action:'view',
                tooltip:'{s name=action/showCustomer}Show customer{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('showCustomer', record);
                },
                // Hide the "view customer" button if the current row does not contain a valid customer
                getClass: function(value, metaData, record) {
                    var customerId = record.get('customerId');
                    if(customerId === Ext.undefined || customerId <= 0) {
                        return 'x-hide-display';
                    }
                }
            },
            {
                iconCls:'sprite-sticky-notes-pin',
                action:'viewOrder',
                tooltip:'{s name=action/showOrder}Show order{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('showOrder', record);
                },
                // Hide the "view customer" button if the current row does not contain a valid customer
                getClass: function(value, metaData, record) {
                    var orderId = record.get('id');
                    if(orderId === Ext.undefined || orderId <= 0) {
                        return 'x-hide-display';
                    }
                }
            }

        ];
    },

    /**
     * Creates the default toolbar and adds the deleteSelectedOrdersButton
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                '->',
                {
                    xtype    : 'textfield',
                    name     : 'searchfield',
                    emptyText: '{s name=searchfield}Search{/s}',
                    cls: 'searchfield',
                    checkChangeBuffer: 700,
                    listeners: {
                        change: function(field, value) {
                            me.fireEvent('searchOrders', field);
                        }
                    }
                }
            ]
        });

        return me.toolbar;

    },

    /**
     * Creates pagingbar
     *
     * @return Array
     */
    getPagingbar: function() {
        var me = this;

        return [{
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        }];
    }
});
//{/block}