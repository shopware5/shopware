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
 * @package    Order
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware Controller - Order backend module
 *
 * The order module main controller handles the initialisation of the order backend module.
 * It is possible to pass a order id to the module to open the detail window directly. To
 * open the detail window directly pass the order id in the parameter "orderId".
 */
//{block name="backend/order/controller/main"}
Ext.define('Shopware.apps.Order.controller.Main', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @params orderId - The main controller can handle a orderId parameter to open the order detail page directly
     * @return void
     */
    init:function () {
        var me = this;

        if (me.subApplication && me.subApplication.params && Ext.isNumeric(me.subApplication.params.orderId)) {
            //open the order detail page with the passed order id
            var store = me.subApplication.getStore('Order'),
                historyStore = Ext.create('Shopware.apps.Order.store.OrderHistory');

            store.getProxy().extraParams.orderID = me.subApplication.params.orderId;
            historyStore.getProxy().extraParams.orderID = me.subApplication.params.orderId;

            store.load({
                callback:function (records) {
                    var order = records[0];
                    var listStore = Ext.create('Shopware.apps.Order.store.ListBatch');
                    store.getProxy().extraParams.orderID = null;
                    listStore.load({
                        callback:function (records) {
                            var record = records[0];
                            var stores = me.getAssociationStores(record);

                            me.mainWindow = me.getView('detail.Window').create({
                                record: order,
                                orderStatusStore: stores['orderStatusStore'],
                                paymentStatusStore: stores['paymentStatusStore'],
                                taxStore: me.getStore('Tax'),
                                statusStore: stores['statusStore'],
                                historyStore: historyStore.load()
                            });
                            me.subApplication.setAppWindow(me.mainWindow);
                        }
                    });
                }
            });
        } else {

            var listStore = Ext.create('Shopware.apps.Order.store.ListBatch');
            listStore.load({
                callback:function (records) {
                    var record = records[0];
                    var stores = me.getAssociationStores(record);
                    //open the order listing window
                    me.mainWindow = me.getView('main.Window').create({
                        orderStatusStore: stores['orderStatusStore'],
                        paymentStatusStore: stores['paymentStatusStore'],
                        taxStore: me.getStore('Tax'),
                        statusStore: stores['statusStore'],
                        listStore: me.subApplication.getStore('Order').load(),
                        statisticStore: Ext.create('Shopware.apps.Order.store.Statistic').load()
                    });
                }
            });
        }

        me.callParent(arguments);
    },

    getAssociationStores: function(record) {
        var orderStatusStore = Ext.create('Shopware.apps.Base.store.OrderStatus');
        var paymentStatusStore = Ext.create('Shopware.apps.Base.store.PaymentStatus');
        var statusStore = Ext.create('Shopware.apps.Base.store.PositionStatus');

        orderStatusStore.add(record.raw.orderStatus);
        paymentStatusStore.add(record.raw.paymentStatus);
        statusStore.add(record.raw.positionStatus);

        var stores = [];
        stores['orderStatusStore'] = orderStatusStore;
        stores['statusStore'] = statusStore;
        stores['paymentStatusStore'] = paymentStatusStore;
        
        return stores;
    }
});
//{/block}
