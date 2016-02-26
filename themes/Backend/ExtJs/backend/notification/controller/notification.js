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
 * @package    Notification
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - notification main backend module
 *
 * The notification module notification controller handles all notification functions
 */
//{block name="backend/notification/controller/notification"}
Ext.define('Shopware.apps.Notification.controller.Notification', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Initialize the notification controller and listens and controls the listed events
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'notification-notification-article': {
                showCustomers: me.onShowCustomers
            },
            'notification-notification-customer': {
                openCustomerAccount: me.onOpenCustomerAccount
            },
            'notification-notification-article textfield[action=searchArticle]':{
                change:me.onSearchArticle
            },
            'notification-notification-customer textfield[action=searchCustomer]':{
                change:me.onSearchCustomer
            }
        });
    },

    /**
     * Displays all depended customers on the right side
     *
     * @param [object]  view - The view. Is needed to get the right f
     * @param [integer] rowIndex - The row number
     * @return void
     */
    onShowCustomers:function (view, rowIndex) {
        var me = this,
            customerStore = me.subApplication.customerStore,
            record = me.subApplication.articleStore.getAt(rowIndex);

        // use extraParams to not separately filter for the article order number
        customerStore.getProxy().extraParams = {
            orderNumber:record.get("number")
        };
        customerStore.load();
    },

    /**
     * open the specific customer module page
     *
     * @param view
     * @param rowIndex
     * @return void
     */
    onOpenCustomerAccount:function (view, rowIndex) {
        var me = this;
        var record = me.subApplication.customerStore.getAt(rowIndex);
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Customer',
            action: 'detail',
            params: {
                customerId: record.get("customerId")
            }
        });
    },

    /**
     * Filters the article grid with the passed search value to find the right articles
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchArticle:function (field, value) {
        var me = this,
            searchString = Ext.String.trim(value),
            store = me.subApplication.articleStore;
        store.filters.clear();
        store.currentPage = 1;
        searchString = searchString + "%";
        store.filter('search',searchString);
    },

    /**
     * Filters the customer grid with the passed search value to find the right customers
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchCustomer:function (field, value) {
        var me = this,
            searchString = Ext.String.trim(value),
            store = me.subApplication.customerStore;
        store.filters.clear();
        store.currentPage = 1;
        searchString = "%" + searchString + "%";
        store.filter('search',searchString);
    }
});
//{/block}
