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
 * @package    Shopware_Paypal
 * @subpackage Paypal
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

//{namespace name=backend/payment_paypal/view/main}

/**
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.PaymentPaypal.view.main.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.paypal-main-list',

    store: 'main.List',

//    layout: 'fit',
//    viewConfig: {
//        stripeRows: true
//    },

    initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            dockedItems: [
                me.getPagingToolbar(),
                me.getToolbar()
            ],
            columns: me.getColumns()
        });
        this.callParent();
        me.store.clearFilter(true);
        me.store.load();
    },

    getColumns: function() {
        var me = this;
        return [{
            text: '{s name=list/columns/date_text}Order date{/s}',
            flex: 3,
            xtype: 'datecolumn',
            format: Ext.Date.defaultFormat + ' H:i:s',
            dataIndex: 'orderDate'
        },{
            text: '{s name=list/columns/order_number_text}Order number{/s}',
            flex: 2,
            dataIndex: 'orderNumber'
        },{
            text: '{s name=list/columns/transaction_id_text}Transaction ID{/s}',
            flex: 3,
            dataIndex: 'transactionId'
        },{
            text: '{s name=list/columns/payment_method_text}Payment method{/s}',
            flex: 2,
            dataIndex: 'paymentDescription'
        },{
            text: '{s name=list/columns/customer_text}Customer{/s}',
            flex: 3,
            dataIndex: 'customer'
        },{
            text: '{s name=list/columns/currency_text}Currency{/s}',
            flex: 2,
            dataIndex: 'currency'
        },{
            text: '{s name=list/columns/amount_text}Amount{/s}',
            flex: 2,
            align: 'right',
            renderer : function(value, column, model) {
                return model.data.amountFormat;
            },
            dataIndex: 'amount'
        },{
            text: '{s name=list/columns/order_status_text}Order status{/s}',
            flex: 2,
            dataIndex: 'statusId',
            renderer : function(value, column, model) {
                return model.data.statusDescription;
            }
        },{
            text: '{s name=list/columns/payment_status_text}Payment status{/s}',
            flex: 2,
            dataIndex: 'cleardID',
            renderer : function(value, column, model) {
                return model.data.clearedDescription;
            }
        },{
            xtype:'actioncolumn',
            width: 70,
            sortable: false,
            items: [{

                iconCls: 'sprite-user--pencil',
                tooltip: '{s name=list/actioncolumn/customer_tooltip}Open customer details{/s}',
                handler: function(grid, rowIndex, colIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Customer',
                        action: 'detail',
                        params: {
                            customerId: record.get('customerId')
                        }
                    });
                }
            }, {
                iconCls: 'sprite-receipt-sticky-note',
                tooltip: '{s name=list/actioncolumn/order_tooltip}Open order details{/s}',
                handler: function(grid, rowIndex, colIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Order',
                        params: {
                            orderId: record.get('id')
                        }
                    });
                }
            }, {
                iconCls: 'sprite-receipt-invoice',
                tooltip: '{s name=list/actioncolumn/invoice_tooltip}Open invoice{/s}',
                getClass: function(value, metadata, record) {
                    if(!record.get('invoiceNumber')) {
                        return 'x-hidden';
                    }
                },
                handler: function(grid, rowIndex, colIndex) {
                    var record = grid.getStore().getAt(rowIndex),
                        link = "{url controller=order action=openPdf}"
                            + "?id=" + record.get('invoiceHash');
                    window.open(link, '_blank');
                }
            }]
        }];
    },

    getPagingToolbar: function() {
        var me = this;
        return {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store,
            dock: 'bottom'
        };
    },

    getToolbar: function() {
        var me = this;
        return {
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'top',
            border: false,
            items: me.getTopBar()
        };
    },

    getTopBar:function () {
        var me = this;
        var items = [];
        items.push({
            fieldLabel: '{s name=list/balance/label}Your PayPal balance{/s}',
            labelWidth: 150,
            xtype: 'textfield',
            readOnly: true,
            name: 'balance'
        }, '->', {
            xtype: 'textfield',
            name:'searchfield',
            cls:'searchfield',
            width:100,
            emptyText:'{s name=list/search/text}Search...{/s}',
            enableKeyEvents:true,
            checkChangeBuffer:500
        }, {
            xtype:'tbspacer', width:6
        });
        return items;
    }
});