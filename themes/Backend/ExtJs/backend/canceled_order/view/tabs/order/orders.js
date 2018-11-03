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
 * @package    CanceledOrder
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/canceled_order/view/main}

/**
 * Shopware UI - Tab showing canceled orders
 * This tab holds a grid displaying canceled orders
 */
//{block name="backend/canceled_order/view/tabs/orders"}
Ext.define('Shopware.apps.CanceledOrder.view.tabs.order.Orders', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.canceled-order-tabs-order-orders',
    region: 'center',

    border: false,

    snippets : {
        columns : {
            orderTime: '{s name=columns/orderDate}Date{/s}',
            amount: '{s name=columns/amount}Amount{/s}',
            contact: '{s name=columns/contact}Contact{/s}',
            transaction: '{s name=columns/transaction}Transaction{/s}',
            payment: '{s name=columns/payment}Payment{/s}',
            customer: '{s name=columns/customer}Customer{/s}',
            action: '{s name=columns/action}Action{/s}',
            deviceType: '{s name=columns/device_type}Device-Type{/s}'
        }
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.selModel = me.getGridSelModel();
        me.columns = me.getColumns();
        me.tbar = me.getToolbar();
        me.bbar = me.getPagingbar();

        // register events
        me.addEvents( 'deleteOrder', 'openOrder', 'contactUser', 'convertOrder' );

        me.callParent(arguments);
    },

    /**
     * Return the selection model for this grid.
     *
     * @return Ext.selection.CheckboxModel
     */
    getGridSelModel : function() {
        var me = this;
        /*{if {acl_is_allowed privilege=delete}}*/
        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    me.deleteSelectedOrdersButton.setDisabled(selections.length == 0);
                }
            }
        });
        /*{/if}*/
    },

    /**
     * Creates the grid columns
     * Data indices where chosen in order to match the database scheme for sorting in the PHP backend.
     * Therefore each Column requieres its own renderer in order to display the correct value.
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.orderTime,
                dataIndex: 'orders.orderTime',
                flex: 1,
                renderer: me.orderTimeRenderer
            },
            {
                header: me.snippets.columns.amount,
                dataIndex: 'orders.invoiceAmount',
                flex: 1,
                renderer: me.amountRenderer,
                align: 'right'
            },
            {
                header: me.snippets.columns.transaction,
                dataIndex: 'orders.transactionId',
                renderer: me.transactionRenderer,
                flex: 1
            },
            {
                header: me.snippets.columns.contact,
                dataIndex: 'orders.comment',
                renderer: me.commentRenderer,
                flex: 2
            },
            {
                header: me.snippets.columns.payment,
                dataIndex: 'payment.name',
                flex: 1,
                renderer: me.paymentRenderer
            },
            {
                header: me.snippets.columns.customer,
                dataIndex: 'customer.lastname',
                flex: 1,
                renderer: me.customerRenderer
            },
            {
                header: me.snippets.columns.deviceType,
                dataIndex: 'orders.deviceType',
                flex: 1,
                renderer: me.deviceTypeRenderer
            },
            {
                xtype : 'actioncolumn',
                width : 80,
                items : me.getActionColumn()
            }
        ];
    },

    /**
     * Returns the transactionId from a record
     *
     * @param value
     * @param metaDate
     * @param record
     * @return string
     */
    commentRenderer: function (value, metaDate, record) {
        if (!record) {
            return '-';
        }
        return record.get('comment');
    },

    /**
     * Returns the transactionId from a record
     *
     * @param value
     * @param metaDate
     * @param record
     * @return string
     */
    transactionRenderer: function(value, metaDate, record){
        if (!record) {
            return '';
        }
        return record.get('transactionId');
    },

    /**
     * Extracts payment description from a given record and returns it. Will return '' in case of empty data
     *
     * @param value
     * @param metaDate
     * @param record
     * @return string
     */
    paymentRenderer: function(value, metaDate, record) {
        if(!record.getPayment() || !record.getPayment().first()) {
            return '';
        }

        return record.getPayment().first().get('description');
    },

    /**
     * Extracts customer's name and last name from a given record and returns it. Returns '' in case of empty data
     *
     * @param value
     * @param metaData
     * @param record
     * @return string
     */
    customerRenderer: function(value, metaData, record) {
        if(!record.getCustomer() || !record.getCustomer().first()) {
            return '';
        }

        var customer = record.getCustomer().first();

        return customer.get('lastname') + ", " + customer.get('firstname');
    },

    /**
     * Formats a given time string and returns it.
     * If  value is neither string nor date, original value will be returned
     *
     * @param value
     * @return mixed
     */
    orderTimeRenderer: function(value, metaData, record) {
        if (!record) {
            return '';
        }
        value = record.get('orderTime');

        if(Ext.isDate(value) || Ext.isString(value)) {
            return Ext.util.Format.date(value)
        }

        return value;
    },

    /**
     * Formats currency.
     * If value is neither string nor number, value will be returned as is
     *
     * @param value
     * @return mixed
     */
    amountRenderer: function(value, metaData, record) {
        if (!record) {
            return '';
        }
        value = record.get('invoiceAmount');

        if(Ext.isNumber(value) || Ext.isString(value)) {
            return Ext.util.Format.currency(value);
        }

        return value
    },

    /**
     * Renders the device type and converts the first letter to upper case
     * @param value
     * @param metaData
     * @param record
     * @return string
     */
    deviceTypeRenderer: function(value, metaData, record) {
        var deviceType = record.get('deviceType');

        if (deviceType.length) {
            return deviceType.charAt(0).toUpperCase() + deviceType.slice(1);
        } else {
            return deviceType;
        }
    },

    /**
     * Returns an array of icons for the action column
     *
     * @return Array of buttons
     */
    getActionColumn : function() {
        var me = this;

        return [
            /*{if {acl_is_allowed privilege=delete}}*/
            {
                iconCls : 'sprite-minus-circle-frame',
                action : 'delete',
                tooltip : '{s name=order_delete_tooltip}Delete{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    var records = [record];
                    me.fireEvent('deleteOrder', records);
                }
            },
            /*{/if}*/
            {
                iconCls:'sprite-arrow-circle',
                action:'convert',
                tooltip:'{s name=order_details_convert}Convert to regular order{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('convertOrder', record);
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



        me.deleteSelectedOrdersButton =Ext.create('Ext.button.Button', {
            text: '{s name=order_delete_button}Delete selected orders{/s}',
            iconCls : 'sprite-minus-circle-frame',
            disabled: true,
            handler: function(){
                var selectionModel = me.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('deleteOrder', records);
                }
            }
        });

        var toolbar = Ext.create('widget.canceled-order-toolbar');
        /*{if {acl_is_allowed privilege=delete}}*/
        toolbar.items.insert(0, me.deleteSelectedOrdersButton);
        /*{/if}*/
        return toolbar;

    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
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
