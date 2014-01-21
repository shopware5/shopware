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
 * Analytics ReferrerRevenue Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * todo@all - documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/referrer_revenue"}
Ext.define('Shopware.apps.Analytics.view.table.ReferrerRevenue', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-referrer_revenue',
    shopColumnName: '{s name=nav/turnover_referrer}Turnover by referrer{/s}',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                align: 'right',
                flex: 1,
                sortable: false
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
                dataIndex: 'host',
                align: 'left',
                text: '{s name=table/referrer_revenue/host}Host{/s}'
            },
            {
                dataIndex: 'entireRevenue',
                text: '{s name=table/referrer_revenue/total_turnover}Total Turnover{/s}'
            },
            {
                dataIndex: 'lead',
                text: '{s name=table/referrer_revenue/lead}Lead-Value{/s}'
            },
            {
                dataIndex: 'customerValue',
                text: '{s name=table/referrer_revenue/customer_value}Customer value{/s}'
            },
            {
                dataIndex: 'entireNewRevenue',
                text: '{s name=table/referrer_revenue/new_turnover}Turnover new customers{/s}'
            },
            {
                dataIndex: 'entireOldRevenue',
                text: '{s name=table/referrer_revenue/old_turnover}Turnover old customers{/s}'
            },
            {
                dataIndex: 'orderCount',
                text: '{s name=table/referrer_revenue/orders}Orders{/s}'
            },
            {
                dataIndex: 'newCustomers',
                text: '{s name=table/referrer_revenue/new_customers}New customers{/s}'
            },
            {
                dataIndex: 'oldCustomers',
                text: '{s name=table/referrer_revenue/old_customers}Old customers{/s}'
            },
            {
                dataIndex: 'perNewRevenue',
                text: '{s name=table/referrer_revenue/turnover_per_new}Turnover/New customer{/s}'
            },
            {
                dataIndex: 'perOldRevenue',
                text: '{s name=table/referrer_revenue/turnover_per_old}Turnover/Old customer{/s}'
            }
        ];
    }
});
//{/block}