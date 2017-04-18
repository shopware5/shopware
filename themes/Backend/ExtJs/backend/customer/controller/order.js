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
 * @package    Customer
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/order}

/**
 * Shopware Controller - Customer list backend module
 *
 * The shopware customer order controller handles all actions around the customer order grid.
 * Listeners:
 *  - Search field => Fired when the user insert a search string into the toolbar search field to filter the order store.
 *  - Action column => Fired when the user clicks on the action column of the order grid to open the order detail window with the selected order.
 *  - Change date => Fired when the user changed a date field of the chart toolbar to filter the chart store.
 */
// {block name="backend/customer/controller/order"}
Ext.define('Shopware.apps.Customer.controller.Order', {

    /**
     * Defines that this component is a extJs controller extension
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Init function of the controller which fires when the user want
     * to access the customer list module.
     * Registers the event listener for the grid action columns
     * and creates and show the customer list window.
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            'customer-order-grid': {
                openOrder: me.onOpenOrder,
                searchOrder: me.onSearchOrder
            },
            'customer-detail-window datefield[name=fromDate]': {
                change: me.onChangeFromDate
            },
            'customer-detail-window datefield[name=toDate]': {
                change: me.onChangeToDate
            }
        });
        me.callParent(arguments);
    },

    /**
     * Event listener method which is fired when the user change
     * the from date field to filter the order chart data.
     * The from date field is placed on top of the chart.
     * @param value
     * @return void
     */
    onChangeToDate: function (field, value) {
        var me = this;
        if (Ext.typeOf(value) != 'date') {
            return;
        }

        var chart = field.up('window').down('chart'),
            store = chart.store;

        store.getProxy().extraParams = {
            customerID: store.getProxy().extraParams.customerID,
            fromDate: store.getProxy().extraParams.fromDate,
            toDate: me.getFormattedDate(value)
        };
        store.load();
    },

    /**
     * Event listener method which is fired when the user change
     * the to date field to filter the order chart data.
     * The to date field is placed on top of the chart.
     * @param [Ext.form.Field.Date] - The date field which changed
     * @param [Ext.Date] - The new value
     * @return void
     */
    onChangeFromDate: function (field, value) {
        var me = this;
        if (Ext.typeOf(value) != 'date') {
            return;
        }

        var chart = field.up('window').down('chart'),
            store = chart.store;

        store.getProxy().extraParams = {
            customerID: store.getProxy().extraParams.customerID,
            toDate: store.getProxy().extraParams.toDate,
            fromDate: me.getFormattedDate(value)
        };
        store.load();
    },

    /**
     * Helper method which creates a formatted
     * date string based on the passed value and separator.
     *
     * @public
     * @param [date] value - Date object
     * @param [string] sep - separator symbol
     * @return [string] formatted string
     */
    getFormattedDate: function(value, sep) {
        var month = value.getMonth(),
            year = value.getFullYear(),
            day = value.getDate();

        sep = sep || '-';
        return year + sep + (month + 1) + sep + day;
    },

    /**
     * Event listener method which is fired when the
     * user edit a customer and clicks on the action column
     * of the order grid to open the order in the order module.
     * @param [object] record - Associated store record
     * @return void
     */
    onOpenOrder: function (record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Order',
            params: {
                orderId: record.get('id')
            }
        });
    },

    /**
     * Event listener method which is fired when the user
     * insert a search string into the text field which is placed
     * on top of the customer order grid.
     * @param [string] value - search string
     * @param [object] store - store that will be filtered
     * @return boolean
     */
    onSearchOrder: function (value, store) {
        var searchString = Ext.String.trim(value);

        // scroll the store to first page
        store.currentPage = 1;

        // If the search-value is empty, reset the filter
        if (searchString.length === 0) {
            store.clearFilter();
        } else {
            // This won't reload the store
            store.filters.clear();
            // Loads the store with a special filter
            store.filter('filter', searchString);
        }

        return true;
    }
});
// {/block}
