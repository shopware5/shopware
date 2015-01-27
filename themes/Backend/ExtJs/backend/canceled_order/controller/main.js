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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/canceled_order/controller/main}

/**
 * Shopware Controller - Main controller
 * The main controller creates the main window
 */
//{block name="backend/canceled_order/controller/main"}
Ext.define('Shopware.apps.CanceledOrder.controller.Main', {

    extend: 'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * A template method that is called when your application boots. It is called before the Application's
     * launch function is executed so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        // Load the stores
        me.subApplication.canceledOrderStore = me.getStore('Order').load();
        me.subApplication.canceledOrderBasket = me.getStore('Basket').load();
        me.subApplication.canceledOrderStatistic = me.getStore('Statistic').load();
        me.subApplication.canceledOrderArticles = me.getStore('Articles').load();
        me.subApplication.canceledOrderVoucher = me.getStore('Voucher').load();
        me.subApplication.canceledOrderViewports = me.getStore('Viewports').load();

        // Create main window, pass stores
        me.mainWindow = me.getView('main.Window').create({
            canceledOrderStore: me.subApplication.canceledOrderStore,
            canceledOrderBasket: me.subApplication.canceledOrderBasket,
            canceledOrderStatistic: me.subApplication.canceledOrderStatistic,
            canceledOrderArticles: me.subApplication.canceledOrderArticles,
            canceledOrderVoucher: me.subApplication.canceledOrderVoucher,
            canceledOrderViewports: me.subApplication.canceledOrderViewports
        });

        me.subApplication.mainWindow = me.mainWindow;
        me.mainWindow.show();
        me.callParent(arguments);
    }


});
//{/block}
