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
        var me = this, impressionTip = { }, visitTip = { };

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

        if (me.shopSelection && me.shopSelection.length > 0) {
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

            impressionTip = {
                width: 580,
                height: 130,
                items: {
                xtype: 'container',
                    layout: 'fit',
                    items: [ me.impressionGrid ]
                },
                renderer: function (storeItem) {
                    this.setTitle(
                        '{s name=chart/visitors/legend_impression}Total impressions{/s} ' +
                        Ext.Date.format(storeItem.get('datum'), 'D, M, Y')
                    );
                    me.getSubShopData(storeItem, 'totalImpressions');
                }
            };

            visitTip = {
                width: 580,
                height: 130,
                items: {
                    xtype: 'container',
                    layout: 'fit',
                    items: [me.visitsGrid]
                },
                renderer: function (storeItem) {
                    this.setTitle(
                        '{s name=chart/visitors/legend_visits}Total visits{/s} ' +
                        Ext.Date.format(storeItem.get('datum'), 'D, M, Y')
                    );
                    me.getSubShopData(storeItem, 'totalVisits');
                }
            };

        } else {
            visitTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.Date.format(storeItem.get('datum'), 'D, M, Y') + ':&nbsp;' +
                        storeItem.get('totalVisits')
                    )
                }
            };

            impressionTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.Date.format(storeItem.get('datum'), 'D, M, Y') + ':&nbsp;' +
                        storeItem.get('totalImpressions')
                    )
                }
            };
        }

        me.series = [
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'totalImpressions',
                    title: '{s name=chart/visitors/legend_impression}Total impressions{/s}'
                },
                impressionTip
            ),
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'totalVisits',
                    title: '{s name=chart/visitors/legend_visits}Total visits{/s}'
                },
                visitTip
            )
        ];

        me.axes.push({
            type: 'Numeric',
            grid: true,
            position: 'left',
            fields: ['totalImpressions', 'totalVisits'],
            title: '{s name=chart/visitors/count}Count{/s}'
        });

        me.callParent(arguments);
    },


    getSubShopData: function(storeItem, field) {
        var me = this, data = [];

        Ext.each(me.shopSelection, function (shopId) {
            var shop = me.shopStore.getById(shopId);

            data.push(
                { name: shop.get('name'), data: storeItem.get(field + shopId) }
            );
        });

        me.tipStore.loadData(data);
    }

});
//{/block}