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
//{block name="backend/order/view/detail/order_history"}
Ext.define('Shopware.apps.Order.view.detail.OrderHistory', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
    */
    alias:'widget.order-history-list',

    /**
     * Set css class
     * @string
     */
    cls:Ext.baseCSSPrefix + 'order-history-grid',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll:true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        columns: {
            user: '{s name=column/user}User{/s}',
            changeDate: '{s name=column/change_date}Change date{/s}',
            previousOrderStatus: '{s name=column/previous_order_status}Previous order status{/s}',
            orderStatus: '{s name=column/order_status}Current order status{/s}',
            previousPaymentStatus: '{s name=column/previous_payment_status}Previous payment status{/s}',
            paymentStatus: '{s name=column/payment_status}Current payment status{/s}'
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
        me.columns =  {
            items: me.getColumns(),
            defaults: { flex: 1 }
        }
        me.pagingbar = me.getPagingBar();
        me.store = me.historyStore;
        me.dockedItems = [  me.pagingbar ];
        me.callParent(arguments);
    },

    /**
     * Creates the paging toolbar for the customer grid to allow
     * and store paging. The paging toolbar uses the same store as the Grid
     *
     * @return Ext.toolbar.Paging The paging toolbar for the customer grid
     */
    getPagingBar:function () {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.historyStore,
            dock:'bottom',
            displayInfo:true
        });

    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;

        var columns = [{
            header: me.snippets.columns.user,
            dataIndex: 'user.name',
            renderer: me.userColumn
        }, {
            header: me.snippets.columns.changeDate,
            dataIndex: 'history.changeDate',
            renderer: me.changeDateColumn
        }, {
            header: me.snippets.columns.previousOrderStatus,
            dataIndex: 'prevOrderStatusId',
            renderer: me.prevOrderStatusColumn
        }, {
            header: me.snippets.columns.orderStatus,
            dataIndex: 'currentOrderStatusId',
            renderer: me.currentOrderStatusColumn
        }, {
            header: me.snippets.columns.previousPaymentStatus,
            dataIndex: 'prevPaymentStatusId',
            renderer: me.prevPaymentStatusColumn
        }, {
            header: me.snippets.columns.paymentStatus,
            dataIndex: 'currentPaymentStatusId',
            renderer: me.currentPaymentStatusColumn
        }];

        return columns;
    },

    /**
     * Renderer function of the user column
     * @param value
     * @param metaData
     * @param record
     */
    userColumn: function(value, metaData, record) {
        return record.get('userName');
    },

    /**
     * Renderer function of the changeDate column
     * @param value
     * @param metaData
     * @param record
     */
    changeDateColumn: function(value, metaData, record) {
        if (record.get('changeDate') === Ext.undefined) {
            return record.get('changeDate');
        }
        return Ext.util.Format.date(record.get('changeDate')) + ' ' + Ext.util.Format.date(record.get('changeDate'), 'H:i:s');
    },

    /**
     * Renderer function of the prevOrderStatus column
     * @param value
     * @param metaData
     * @param record
     */
    prevOrderStatusColumn: function(value, metaData, record) {
        var me = this;

        if (me.orderStatusStore) {
            var orderStatus = me.orderStatusStore.getById(value);
            return orderStatus.get('description');
        } else {
            return value;
        }
    },

    /**
     * Renderer function of the currentOrderStatus column
     * @param value
     * @param metaData
     * @param record
     */
    currentOrderStatusColumn: function(value, metaData, record) {
        var me = this;

        if (me.orderStatusStore) {
            var orderStatus = me.orderStatusStore.getById(value);
            return orderStatus.get('description');
        } else {
            return value;
        }
    },

    /**
     * Renderer function of the prevPaymentStatus column
     * @param value
     * @param metaData
     * @param record
     */
    prevPaymentStatusColumn: function(value, metaData, record) {
        var me = this;

        if (me.paymentStatusStore) {
            var paymentStatus = me.paymentStatusStore.getById(value);
            if (paymentStatus) {
                return paymentStatus.get('description');
            } else {
                return value;
            }
        } else {
            return value;
        }
    },

    /**
     * Renderer function of the currentPaymentStatus column
     * @param value
     * @param metaData
     * @param record
     */
    currentPaymentStatusColumn: function(value, metaData, record) {
        var me = this;

        if (me.paymentStatusStore) {
            var paymentStatus = me.paymentStatusStore.getById(value);
            if (paymentStatus) {
                return paymentStatus.get('description');
            } else {
                return value;
            }
        } else {
            return value;
        }
    }

});
//{/block}
