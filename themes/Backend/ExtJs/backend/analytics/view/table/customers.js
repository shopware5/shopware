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
 * Analytics Customers Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/customers"}
Ext.define('Shopware.apps.Analytics.view.table.Customers', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-customers',
    shopColumnName: '{s name="nav/customers"}Portion New-/RegularCustomer{/s}',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                align: 'right',
                sortable: false
            }
        };

        me.callParent(arguments);
    },

    createPagingbar: function() {},

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var percentRenderer = function(value) {
            return Ext.util.Format.number(value, '0.00') + ' %';
        };

        return [
            {
                xtype: 'datecolumn',
                dataIndex: 'week',
                text: '{s name="table/customers/day"}Day{/s}',
                format: timeFormat,
                width: 120,
                align: 'left',
                renderer: function(value) {
                    if (value == null) {
                        var date = new Date(2000,1,1,1,0,0);
                        return Ext.util.Format.date(date);
                    }
                    return Ext.util.Format.date(value);
                }
            },
            {
                dataIndex: 'orderCount',
                width: 150,
                text: '{s name="table/customers/order_count"}Order count{/s}'
            },
            {
                dataIndex: 'registration',
                width: 90,
                text: '{s name="table/customers/registration"}Registrations{/s}'
            },
            {
                dataIndex: 'newCustomersPercent',
                flex: 1,
                text: '{s name="table/customers/new_customers"}New customers{/s}',
                renderer: percentRenderer
            },
            {
                dataIndex: 'oldCustomersPercent',
                flex: 1,
                text: '{s name="table/customers/regular_customers"}Regular customers{/s}',
                renderer: percentRenderer
            },
            {
                dataIndex: 'malePercent',
                flex: 1,
                text: '{s name="table/customers/male_portion"}Percentage male{/s}',
                renderer: percentRenderer
            },
            {
                dataIndex: 'femalePercent',
                flex: 1,
                text: '{s name="table/customers/female_portion"}Percentage female{/s}',
                renderer: percentRenderer
            }
        ];
    }
});
//{/block}
