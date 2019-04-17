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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order list backend module
 *
 * The order list view displays the data of the list store.
 * One row displays the head data of a order.
 */
//{block name="backend/order/view/batch/list"}
Ext.define('Shopware.apps.Order.view.batch.List', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
    */
    alias:'widget.batch-list',

    /**
     * Set css class
     * @string
     */
    cls:Ext.baseCSSPrefix + 'order-batch-grid',

    minHeight: 150,
    /**
     * This configuration option is to be applied to child items of the container managed by this layout.
     * Each child item with a flex property will be flexed horizontally according to each item's relative
     * flex value compared to the sum of all items with a flex value specified. Any child items that have either
     * a flex = 0 or flex = undefined will not be 'flexed' (the initial size will not be changed).
     */
    flex: 1,

    /**
     * True to use overflow:'auto' on the components layout element and show scroll
     * bars automatically when necessary, false to clip any overflowing content.
     * @string
     */
    autoScroll:true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=column/number}Order number{/s}',
        columns: {
            number:'{s name=column/number}Order number{/s}',
            customer:'{s name=column/customer}Customer{/s}',
            orderStatus:'{s name=column/order_status}Order Status{/s}',
            paymentStatus:'{s name=column/payment_status}Payment Status{/s}',
            sent:'{s name=column/email_sent}Email has been sent{/s}',
            orderTime:'{s name=column/order_time}Order time{/s}',
            mailGenerated: '{s name=column/mail_generated}Mail has been generated{/s}'
        }
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;

        me.columns = me.getColumns();
        me.callParent(arguments);
    },

    /**
     * Returns an array for the grid columns.
     * @return array
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.number,
                dataIndex: 'number',
                flex:1
            },
            {
                header: me.snippets.columns.orderTime,
                xtype: 'datecolumn',
                dataIndex: 'orderTime',
                flex:1
            },
            {
                header: me.snippets.columns.orderStatus,
                dataIndex: 'orderStatus',
                flex:1,
                renderer: me.orderStatusColumn
            },
            {
                header: me.snippets.columns.paymentStatus,
                dataIndex: 'paymentStatus',
                flex:1,
                renderer: me.paymentStatusColumn
            },
            {
                xtype: 'booleancolumn',
                header: me.snippets.columns.mailGenerated,
                dataIndex: 'mailGenerated',
                flex:1,
                renderer: me.mailColumn
            },
            {
                xtype: 'booleancolumn',
                header: me.snippets.columns.sent,
                dataIndex: 'sent',
                flex:1,
                renderer: me.sentColumn
            }
        ]
    },

    /**
     * Column renderer function for the mail column.
     *
     * @param { boolean } value
     * @param { Object } metaData
     * @param { Ext.data.Model } record
     * @return string
     */
    mailColumn: function(value, metaData, record) {
        var me = this,
            checked = 'sprite-ui-check-box-uncheck',
            mail = record.getMail().first();

        if (mail instanceof Ext.data.Model) {
            checked = 'sprite-ui-check-box';
        }
        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + checked + '"></span>';
    },

    /**
     * Column renderer function for the sent column
     *
     * @param { boolean } value
     * @param { Object } metaData
     * @param { Ext.data.Model } record
     * @return string
     */
    sentColumn: function(value, metaData, record) {
        var me = this,
            checked = 'sprite-ui-check-box-uncheck',
            mail = record.getMail().first();

        if (mail) {
            checked = 'sprite-ui-check-box';
        }
        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + checked + '"></span>';
    },

    /**
     * Column renderer function for the payment column of the list grid.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     * return string
     */
    orderStatusColumn: function(value, metaData, record) {
        var orderStatus = record.getOrderStatus().first();

        if (orderStatus instanceof Ext.data.Model) {
            return orderStatus.get('description');
        } else {
            return value;
        }
    },

    /**
     * Column renderer function for the payment column of the list grid.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     * @return string
     */
    paymentStatusColumn: function(value, metaData, record) {
        var paymentStatus = null;

        if (record && record.getPaymentStatus() instanceof Ext.data.Store && record.getPaymentStatus().first() instanceof Ext.data.Model) {
            paymentStatus = record.getPaymentStatus().first();
        }

        if (paymentStatus instanceof Ext.data.Model) {
            return paymentStatus.get('description');
        } else {
            return value;
        }
    }

});
//{/block}
