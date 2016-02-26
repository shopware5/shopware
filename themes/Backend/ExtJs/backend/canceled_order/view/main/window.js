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
 * Shopware UI - Main window of this app
 * Main window will be displayed after the user starts the application
 */
//{block name="backend/canceled_order/view/main/window"}
Ext.define('Shopware.apps.CanceledOrder.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.canceled-order-main-window',
    layout: 'fit',
    width: 1250,
    height: '90%',
    stateful: true,
    stateId: 'shopware-canceledOrders-main-window',

    /**
     * Contains the snippets for this component
     */
    snippets: {
        title: '{s name=window/title}Canceled Orders{/s}',
        trustedShop: '{s name=trustedShopMessageNew}Due to the trusted shop certification you may only use the features &quot;Ask for reason&quot; and &quot;Send coupon&quot; if you have the customers agreement.{/s}'
    },

    /**
     * Init the component, add noticeContainer and Tabs
     */
    initComponent: function() {
        /**
         * Initializes the component, adds NoticeContainer and tabs
         *
         * @return void
         */
        var me = this;
        me.dockedItems = me.createNoticeContainer();

        me.items = me.createTab();
        me.title = me.snippets.title;
        me.callParent(arguments);
    },

    /**
     * Creates the notice container which is displayed in the top of the window.
     * @return object
     */
    createNoticeContainer: function() {
        var me = this;

        var notification = Shopware.Notification.createBlockMessage(me.snippets.trustedShop, 'notice');
        notification.margin = '10 5';
        return notification;
    },

    /**
     * Creates the tab panel for the main window
     */
    createTab: function() {
        var me = this;

        me.tabPanel =  Ext.create('Ext.tab.Panel', {
            items: me.getTabs()
        });
        return me.tabPanel;
    },

    /**
     * Creates the main tab
     * internal titles needed in the main controller to tell apart the different tabs
     * @return Array
     */
    getTabs: function(){
        var me = this;

        return [{
            xtype:'canceled-order-tabs-order-main',
            canceledOrderStore: me.canceledOrderStore,
            canceledOrderVoucher: me.canceledOrderVoucher,
            internalTitle: 'orders'
        },
        {
            xtype:'canceled-order-tabs-baskets-main',
            overviewStore: me.canceledOrderBasket,
            articlesStore: me.canceledOrderArticles,
            viewportStore: me.canceledOrderViewports,
            internalTitle: 'baskets'
        },
        {
            xtype:'canceled-order-tabs-statistics',
            store: me.canceledOrderStatistic,
            internalTitle: 'statistics'
        }

        ];

    }
});
//{/block}
