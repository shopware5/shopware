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
 * Analytics Month Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/visitors"}
Ext.define('Shopware.apps.Analytics.view.chart.Visitors', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-visitors',
    legend: {
        position: 'right'
    },


    initComponent: function () {
        var me = this;

        me.axes = [
            {
                type: 'Time',
                position: 'bottom',
                fields: ['datum'],
                title: '{s name=chart/visitors/titleBottom}Month{/s}',
                step: [ Ext.Date.DAY, 1 ],
                dateFormat: 'D, M, Y',
                label: {
                    rotate: {
                        degrees: 315
                    }
                }
            }
        ];

        me.series = [];

        me.tipStore = Ext.create('Ext.data.JsonStore', {
            fields: ['name', 'data']
        });

        me.impressionGrid = {
            xtype: 'grid',
            store: me.tipStore,
            height: 130,
            flex: 1,
            columns: [
                {
                    text: '{s name="visitors/chart/tip/name"}Shop{/s}',
                    dataIndex: 'name',
                    flex: 1
                },
                {
                    xtype: 'numbercolumn',
                    text: '{s name=visitors/chart/tip/impressions}Impressions{/s}',
                    dataIndex: 'data',
                    align: 'right',
                    flex: 1
                }
            ]
        };
        me.visitsGrid = {
            xtype: 'grid',
            store: me.tipStore,
            height: 130,
            flex: 1,
            columns: [
                {
                    text: '{s name="visitors/chart/tip/name"}Shop{/s}',
                    dataIndex: 'name',
                    flex: 1
                },
                {
                    xtype: 'numbercolumn',
                    text: '{s name=visitors/chart/tip/visits}Visits{/s}',
                    dataIndex: 'data',
                    align: 'right',
                    flex: 1
                }
            ]
        };


        me.series = [
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'totalImpressions',
                    title: '{s name=chart/visitors/legend_impression}Total impressions{/s}'
                },
                {
                    width: 580,
                    height: 130,
                    items: {
                        xtype: 'container',
                        layout: 'fit',
                        items: [me.impressionGrid]
                    },
                    renderer: function (storeItem) {
                        this.setTitle('{s name=chart/visitors/legend_impression}Total impressions{/s}');
                        me.getSubShopData(storeItem, 'totalImpressions');
                    }
                }
            ),
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'totalVisits',
                    title: '{s name=chart/visitors/legend_visits}Total visits{/s}'
                },
                {
                    width: 580,
                    height: 130,
                    items: {
                        xtype: 'container',
                        layout: 'fit',
                        items: [me.visitsGrid]
                    },
                    renderer: function (storeItem) {
                        this.setTitle('{s name=chart/visitors/legend_visits}Total visits{/s}');
                        me.getSubShopData(storeItem, 'totalVisits');
                    }
                }
            )
        ];

        me.axes.push({
            type: 'Numeric',
            grid: true,
            position: 'left',
            fields: ['totalImpressions', 'totalVisits'],
            title: '{s name=chart/visitors/y_axes}Sales{/s}'
        });

        me.callParent(arguments);
    },


    getSubShopData: function(storeItem, field) {
        var me = this, data = [];

        me.shopStore.each(function (shop) {
            var id = shop.get('id');

            data.push(
                { name: shop.get('name'), data: storeItem.get(field + id) }
            );
        });
        me.tipStore.loadData(data);
    }

});
//{/block}