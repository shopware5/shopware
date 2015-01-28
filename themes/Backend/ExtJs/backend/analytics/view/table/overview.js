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
 * Analytics Overview Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
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
        var me = this;

        return [
            {
                xtype: 'datecolumn',
                dataIndex: 'date',
                align: 'left',
                text: '{s name="table/quick_overview/date"}Date{/s}'
            },
            {
                dataIndex: 'orderCount',
                text: '{s name="table/quick_overview/orders"}Orders{/s}'
            },
            {
                dataIndex: 'conversion',
                text: '{s name="table/quick_overview/conversion_rate"}Conversion Rate{/s}',
                renderer: me.percentRenderer
            },
            {
                dataIndex: 'turnover',
                text: '{s name="general/turnover"}Turnover{/s}',
                renderer: function(value) {

                    return Ext.util.Format.currency(
                        value,
                        me.subApp.currencySign,
                        2,
                        (me.subApp.currencyAtEnd == 1)
                    );
                }
            },
            {
                dataIndex: 'registrations',
                text: '{s name="table/quick_overview/registrations"}New users{/s}'
            },
            {
                dataIndex: 'customers',
                text: '{s name="table/quick_overview/customers"}New customers{/s}'
            },
            {
                dataIndex: 'visits',
                text: '{s name="table/quick_overview/visitors"}Visitors{/s}'
            },
            {
                dataIndex: 'clicks',
                text: '{s name="table/quick_overview/page_calls"}Impressions{/s}'
            }
        ];
    },

    percentRenderer: function(value) {
        return value + ' %';
    }
});

//{/block}
