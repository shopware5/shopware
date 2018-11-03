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
 * Analytics Customers Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/customers"}
Ext.define('Shopware.apps.Analytics.view.chart.Customers', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-customers',
    legend: {
        position: 'right'
    },

    axes: [
        {
            type: 'Numeric',
            minimum: 0,
            grid: true,
            position: 'left',
            fields: ['newCustomersPercent', 'oldCustomersPercent'],
            title: '{s name=chart/customers/percent/title}Percent{/s}'
        },
        {
            type: 'Category',
            title: '{s name=chart/customers/days/title}Days{/s}',
            position: 'bottom',
            fields: ['week'],
            label: {
                rotate: {
                    degrees: 315
                },
                renderer:function (value) {
                    return Ext.util.Format.date(value);
                }
            }
        }
    ],

    initComponent: function () {
        var me = this;


        me.series = [
            me.createLineSeries(
                { xField: 'week', yField: 'newCustomersPercent', title: '{s name="chart/customers/new_customers_legend"}New customers{/s}' },
                {
                    width: 210,
                    height: 45,
                    renderer: function(storeItem) {
                        var data = Ext.util.Format.number(storeItem.get('newCustomersPercent'), '0.00') + ' %';
                        this.setTitle(Ext.util.Format.date(storeItem.get('week')) + ': ' + data);
                    }
                }
            ),
            me.createLineSeries(
                { xField: 'week', yField: 'oldCustomersPercent', title: '{s name="chart/customers/old_customers_legend"}Old customers{/s}' },
                {
                    width: 210,
                    height: 45,
                    renderer: function(storeItem) {
                        var data = Ext.util.Format.number(storeItem.get('oldCustomersPercent'), '0.00') + ' %';
                        this.setTitle(Ext.util.Format.date(storeItem.get('week')) + ': ' + data);
                    }
                }
            )
        ];

        me.callParent(arguments);
    }
});
//{/block}
