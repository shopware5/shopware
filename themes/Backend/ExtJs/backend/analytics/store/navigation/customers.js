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
 */

/**
 * Analytics Customers Store
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{block name="backend/analytics/store/navigation/customers"}
Ext.define('Shopware.apps.Analytics.store.navigation.Customers', {
    extend: 'Ext.data.Store',
    alias: 'widget.analytics-store-navigation-customers',
    remoteSort: true,
    fields: [
        { name: 'week', type: 'timestamp' },
        { name: 'male', type: 'int', defaultValue: 0 },
        { name: 'female', type: 'int', defaultValue: 0 },
        { name: 'registration', type: 'int', defaultValue: 0 },
        { name: 'newCustomersOrders', type: 'int', defaultValue: 0 },
        { name: 'oldCustomersOrders', type: 'int', defaultValue: 0 },
        { name: 'orderCount', type: 'int', defaultValue: 0 },

        { name: 'oldCustomersPercent', type: 'float', convert: function(value, record) {
            var male = record.get('oldCustomersOrders');
            var order = record.get('orderCount');

            return male / order * 100;
        } },


        { name: 'newCustomersPercent', type: 'float', convert: function(value, record) {
            var male = record.get('newCustomersOrders');
            var order = record.get('orderCount');

            return male / order * 100 ;
        } },

        { name: 'malePercent', type: 'float', convert: function(value, record) {
            var male = record.get('male');
            var order = record.get('orderCount');

            return male / order * 100;
        } },
        { name: 'femalePercent', type: 'float', convert: function(value, record) {
            var female = record.get('female');
            var order = record.get('orderCount');

            return female / order * 100;
        } }


    ],
    proxy: {
        type: 'ajax',
        url: '{url controller=analytics action=getCustomers}',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}
