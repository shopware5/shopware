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
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Application - CanceledOrder.
 * Lists orders and baskets with a status -1 ans shows some statistics
 */
//{block name="backend/canceled_order/app"}
Ext.define('Shopware.apps.CanceledOrder', {

    name: 'Shopware.apps.CanceledOrder',

    extend: 'Enlight.app.SubApplication',

    loadPath: '{url action=load}',

    bulkLoad: true,

    controllers: [ 'Main', 'Order', 'Basket' ],

    stores: [ 'Order', 'Basket', 'Statistic', 'Articles', 'Voucher', 'Viewports' ],

    models: [ 'Order', 'Position', 'Basket', 'Statistic', 'Articles', 'Voucher', 'Viewports' ],

    views: [ 'main.Window', 'tabs.order.Orders', 'tabs.baskets.Main', 'tabs.baskets.Overview', 'tabs.baskets.Articles', 'tabs.Statistics', 'Toolbar', 'tabs.order.Main', 'tabs.order.Detail', 'tabs.order.Position', 'tabs.baskets.Viewports' ],

    /**
     * Returns the main application window for this is expected
     * by the Enlight.app.SubApplication class.
     *
     * @private
     * @return [object] mainWindow - the main application window based on Enlight.app.Window
     */
    launch: function() {
        var me             = this,
            mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});
//{/block}
