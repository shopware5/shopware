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
 * Analytics ReferrerRevenue Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
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

    createPagingbar: function() {},

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [
            {
                dataIndex: 'host',
                align: 'left',
                text: '{s name=table/referrer_revenue/host}Host{/s}'
            },
            {
                dataIndex: 'orderCount',
                text: '{s name=table/referrer_revenue/orders}Orders{/s}'
            },
            {
                dataIndex: 'turnover',
                text: '{s name=general/turnover}Turnover{/s}',
                renderer: me.currencyRenderer
            },
            {
                dataIndex: 'average',
                text: '{s name=table/referrer_revenue/average}Ø Order value{/s}',
                renderer: me.currencyRenderer
            },

            {
                dataIndex: 'newCustomers',
                text: '{s name=table/referrer_revenue/new_customers}Orders new customers{/s}'
            },
            {
                dataIndex: 'turnoverNewCustomer',
                text: '{s name=table/referrer_revenue/new_turnover}Turnover new customers{/s}',
                renderer: me.currencyRenderer
            },
            {
                dataIndex: 'averageNewCustomer',
                text: '{s name=table/referrer_revenue/average_new_customers}Ø New customer<br>order value{/s}',
                renderer: me.currencyRenderer
            },

            {
                dataIndex: 'regularCustomers',
                text: '{s name=table/referrer_revenue/old_customers}Orders Regular customers{/s}'
            },
            {
                dataIndex: 'turnoverRegularCustomer',
                text: '{s name=table/referrer_revenue/old_turnover}Turnover regular customers{/s}',
                renderer: me.currencyRenderer
            },
            {
                dataIndex: 'averageRegularCustomer',
                text: '{s name=table/referrer_revenue/average_old_customers}Ø Regular customer<br>order value{/s}',
                renderer: me.currencyRenderer
            }
        ];
    },

    currencyRenderer: function(value) {
        var me = this;

        return Ext.util.Format.currency(
            value,
            me.subApp.currencySign,
            2,
            (me.subApp.currencyAtEnd == 1)
        );
    }
});
//{/block}
