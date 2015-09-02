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
 * Shopware UI - Main Order UI with grid panel and sidebar
 * This tab holds a grid displaying canceled orders
 */
//{block name="backend/canceled_order/view/tabs/order/main"}
Ext.define('Shopware.apps.CanceledOrder.view.tabs.order.Main', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.canceled-order-tabs-order-main',
    title: '{s name=canceledOrders}Canceled Orders{/s}',
    layout: 'border',

    defaults: {
        bodyBorder: 0
    },

    snippets :  {
        sidebar: {
            title: '{s name=order/sideBar/title}Details{/s}'
        }
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create the items of the container
        me.items = me.createItems();

        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;
        return [
            {
                xtype:'canceled-order-tabs-order-orders',
                store: me.canceledOrderStore,
                internalTitle: 'orders'
            },
            {
                xtype: 'canceled-order-view-order-detail',
                canceledOrderVoucher: me.canceledOrderVoucher
            },
            {
                xtype:'canceled-order-view-order-position',
                internalTitle: 'orders'
            }
        ];
    }

});
//{/block}
