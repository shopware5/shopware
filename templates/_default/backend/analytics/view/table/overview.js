/**
 * Shopware 4
 * Copyright © shopware AG
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
 * Analytics Overview Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * todo@all - documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/overview"}
Ext.define('Shopware.apps.Analytics.view.table.Overview', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-overview',
    shopColumnName: '{s name="nav/quick_overview"}Quick-Overview{/s}',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                align: 'right',
                flex: 1
            }
        };

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        return [
            {
                xtype: 'datecolumn',
                dataIndex: 'date',
                text: '{s name="table/quick_overview/date"}Date{/s}'
            },
            {
                dataIndex: 'orderCount',
                text: '{s name="table/quick_overview/orders"}Orders{/s}'
            },
            {
                dataIndex: 'totalConversion',
                text: '{s name="table/quick_overview/conversion_rate"}Conversion Rate{/s}'
            },
            {
                dataIndex: 'revenue',
                text: '{s name="table/quick_overview/turnover"}Turnover{/s}'
            },
            {
                dataIndex: 'cancelledOrders',
                text: '{s name="table/quick_overview/cancelled_baskets"}Cancelled baskets{/s}'
            },
            {
                dataIndex: 'newCustomers',
                text: '{s name="table/quick_overview/new_customers"}New Customers{/s}'
            },
            {
                dataIndex: 'visitors',
                text: '{s name="table/quick_overview/visitors"}Visitors{/s}'
            },
            {
                dataIndex: 'clicks',
                text: '{s name="table/quick_overview/page_calls"}Page calls{/s}'
            }
        ];
    }
});
//{/block}