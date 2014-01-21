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
 * Analytics Customers Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * todo@all - documentation
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
            fields: ['newCustomersOrders'],
            title: '{s name=chart/customers/count/title}Count{/s}'
        },
        {
            type: 'Category',
            position: 'bottom',
            fields: ['week'],
            title: '{s name=chart/customers/week/title}Calender week{/s}'
        }
    ],

    initComponent: function () {
        var me = this;

        me.tipStoreTable = Ext.create('Ext.data.JsonStore', {
            fields: ['name', 'count', 'percentage']
        });

        me.tipGrid = {
            xtype: 'grid',
            height: 130,
            store: me.tipStoreTable,
            flex: 1,
            columns: [
                {
                    text: '{s name="chart/customers/name"}Name{/s}',
                    dataIndex: 'name',
                    flex: 1
                },
                {
                    xtype: 'numbercolumn',
                    text: '{s name="chart/customers/count"}Count{/s}',
                    dataIndex: 'count',
                    align: 'right',
                    flex: 1
                },
                {
                    xtype: 'numbercolumn',
                    text: '{s name="chart/customers/percentage"}Percentage{/s}',
                    dataIndex: 'percentage',
                    align: 'right',
                    flex: 1
                }
            ]
        };

        var tips = {
            width: 580,
            height: 130,
            items: {
                xtype: 'container',
                layout: 'hbox',
                items: [ me.tipGrid ]
            },
            renderer: function (cls, item) {
                me.renderMaleData(item, this);
            }
        };

        me.series = [
            me.createLineSeries(
                { xField: 'week', yField: 'newCustomersOrders', title: '{s name="chart/customers/new_customers_legend"}New customers{/s}' },
                tips
            ),
            me.createLineSeries(
                { xField: 'week', yField: 'oldCustomersOrders', title: '{s name="chart/customers/old_customers_legend"}Old customers{/s}' },
                tips
            )
        ];

        me.callParent(arguments);
    },

    renderMaleData: function (item, tip) {
        var me = this, data = [],
            storeItem = item.storeItem;

        data.push({
            name: 'female',
            count: storeItem.get('female'),
            percentage: storeItem.get('femaleAmount')
        });
        data.push({
            name: 'male',
            count: storeItem.get('male'),
            percentage: storeItem.get('maleAmount')
        });

        me.tipStoreTable.loadData(data);
        tip.setTitle(storeItem.get('week'));
    }
});
//{/block}
