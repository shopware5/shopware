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
        var me = this,
            desktopImpressionTip = { },
            tabletImpressionTip = { },
            mobileImpressionTip = { },
            totalImpressionTip = { },
            desktopVisitTip = { },
            tabletVisitTip = { },
            mobileVisitTip = { },
            totalVisitTip = { };

        me.axes = [
            {
                type: 'Time',
                position: 'bottom',
                fields: ['datum'],
                title: '{s name=chart/visitors/titleBottom}Month{/s}',
                step: [ Ext.Date.DAY, 1 ],
                dateFormat: Ext.util.Format.dateFormat,
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

            desktopImpressionTip = {
                width: 580,
                height: 130,
                items: {
                xtype: 'container',
                    layout: 'fit',
                    items: [ me.impressionGrid ]
                },
                renderer: function (storeItem) {
                    this.setTitle(
                        '{s name=chart/visitors/legend_desktop_impression}Desktop impressions{/s} ' +
                            Ext.util.Format.date(storeItem.get('datum'))
                    );
                    me.getSubShopData(storeItem, 'desktopImpressions');
                }
            };
            tabletImpressionTip = {
                width: 580,
                height: 130,
                items: {
                    xtype: 'container',
                    layout: 'fit',
                    items: [ me.impressionGrid ]
                },
                renderer: function (storeItem) {
                    this.setTitle(
                        '{s name=chart/visitors/legend_tablet_impression}Tablet impressions{/s} ' +
                            Ext.util.Format.date(storeItem.get('datum'))
                    );
                    me.getSubShopData(storeItem, 'tabletImpressions');
                }
            };
            mobileImpressionTip = {
                width: 580,
                height: 130,
                items: {
                    xtype: 'container',
                    layout: 'fit',
                    items: [ me.impressionGrid ]
                },
                renderer: function (storeItem) {
                    this.setTitle(
                        '{s name=chart/visitors/legend_mobile_impression}Mobile impressions{/s} ' +
                            Ext.util.Format.date(storeItem.get('datum'))
                    );
                    me.getSubShopData(storeItem, 'mobileImpressions');
                }
            };
            totalImpressionTip = {
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
                            Ext.util.Format.date(storeItem.get('datum'))
                    );
                    me.getSubShopData(storeItem, 'totalImpressions');
                }
            };

            desktopVisitTip = {
                width: 580,
                height: 130,
                items: {
                    xtype: 'container',
                    layout: 'fit',
                    items: [ me.visitsGrid ]
                },
                renderer: function (storeItem) {
                    this.setTitle(
                        '{s name=chart/visitors/legend_desktop_visit}Desktop visits{/s} ' +
                            Ext.util.Format.date(storeItem.get('datum'))
                    );
                    me.getSubShopData(storeItem, 'desktopVisits');
                }
            };
            tabletVisitTip = {
                width: 580,
                height: 130,
                items: {
                    xtype: 'container',
                    layout: 'fit',
                    items: [ me.visitsGrid ]
                },
                renderer: function (storeItem) {
                    this.setTitle(
                        '{s name=chart/visitors/legend_tablet_visit}Tablet visits{/s} ' +
                            Ext.util.Format.date(storeItem.get('datum'))
                    );
                    me.getSubShopData(storeItem, 'tabletVisits');
                }
            };
            mobileVisitTip = {
                width: 580,
                height: 130,
                items: {
                    xtype: 'container',
                    layout: 'fit',
                    items: [ me.visitsGrid ]
                },
                renderer: function (storeItem) {
                    this.setTitle(
                        '{s name=chart/visitors/legend_mobile_visit}Mobile visits{/s} ' +
                            Ext.util.Format.date(storeItem.get('datum'))
                    );
                    me.getSubShopData(storeItem, 'mobileVisits');
                }
            };
            totalVisitTip = {
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
                            Ext.util.Format.date(storeItem.get('datum'))
                    );
                    me.getSubShopData(storeItem, 'totalVisits');
                }
            };

        } else {
            desktopVisitTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.util.Format.date(storeItem.get('datum')) + ':&nbsp;' +
                        storeItem.get('desktopVisits')
                    )
                }
            };
            tabletVisitTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.util.Format.date(storeItem.get('datum')) + ':&nbsp;' +
                        storeItem.get('tabletVisits')
                    )
                }
            };
            mobileVisitTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.util.Format.date(storeItem.get('datum')) + ':&nbsp;' +
                        storeItem.get('mobileVisits')
                    )
                }
            };
            totalVisitTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.util.Format.date(storeItem.get('datum')) + ':&nbsp;' +
                        storeItem.get('totalVisits')
                    )
                }
            };

            desktopImpressionTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.util.Format.date(storeItem.get('datum')) + ':&nbsp;' +
                        storeItem.get('desktopImpressions')
                    )
                }
            };
            tabletImpressionTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.util.Format.date(storeItem.get('datum')) + ':&nbsp;' +
                            storeItem.get('tabletImpressions')
                    )
                }
            };

            mobileImpressionTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.util.Format.date(storeItem.get('datum')) + ':&nbsp;' +
                            storeItem.get('mobileImpressions')
                    )
                }
            };
            totalImpressionTip = {
                width: 180,
                height: 30,
                renderer: function(storeItem) {
                    this.setTitle(
                        Ext.util.Format.date(storeItem.get('datum')) + ':&nbsp;' +
                            storeItem.get('totalImpressions')
                    )
                }
            };
        }

        me.series = [
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'desktopImpressions',
                    title: '{s name=chart/visitors/legend_desktop_impression}Desktop impressions{/s}',
                    markerConfig: {
                        type: 'cross',
                        fill: '#00CC66',
                        stroke: '#00CC66'
                    },
                    style: {
                        stroke: '#00CC66',
                        fill: '#00CC66'
                    }
                },
                desktopImpressionTip
            ),
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'tabletImpressions',
                    title: '{s name=chart/visitors/legend_tablet_impression}Tablet impressions{/s}',
                    markerConfig: {
                        type: 'cross',
                        fill: '#9955FF',
                        stroke: '#9955FF'
                    },
                    style: {
                        stroke: '#9955FF',
                        fill: '#9955FF'
                    }
                },
                tabletImpressionTip
            ),
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'mobileImpressions',
                    title: '{s name=chart/visitors/legend_mobile_impression}Mobile impressions{/s}',
                    markerConfig: {
                        type: 'cross',
                        fill: '#FF6600',
                        stroke: '#FF6600'
                    },
                    style: {
                        stroke: '#FF6600',
                        fill: '#FF6600'
                    }
                },
                mobileImpressionTip
            ),
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'totalImpressions',
                    title: '{s name=chart/visitors/legend_impression}Total impressions{/s}',
                    markerConfig: {
                        type: 'cross',
                        fill: '#0099FF',
                        stroke: '#0099FF'
                    },
                    style: {
                        stroke: '#0099FF',
                        fill: '#0099FF'
                    }
                },
                totalImpressionTip
            ),
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'desktopVisits',
                    title: '{s name=chart/visitors/legend_desktop_visits}Desktop visits{/s}',
                    markerConfig: {
                        type: 'circle',
                        fill: '#00CC66',
                        stroke: '#00CC66'
                    },
                    style: {
                        stroke: '#00CC66',
                        fill: '#00CC66'
                    }
                },
                desktopVisitTip
            ),
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'tabletVisits',
                    title: '{s name=chart/visitors/legend_tablet_visits}Tablet visits{/s}',
                    markerConfig: {
                        type: 'circle',
                        fill: '#9955FF',
                        stroke: '#9955FF'
                    },
                    style: {
                        stroke: '#9955FF',
                        fill: '#9955FF'
                    }
                },
                tabletVisitTip
            ),
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'mobileVisits',
                    title: '{s name=chart/visitors/legend_mobile_visits}Mobile visits{/s}',
                    markerConfig: {
                        type: 'circle',
                        fill: '#FF6600',
                        stroke: '#FF6600'
                    },
                    style: {
                        stroke: '#FF6600',
                        fill: '#FF6600'
                    }
                },
                mobileVisitTip
            ),
            me.createLineSeries(
                {
                    xField: 'datum',
                    yField: 'totalVisits',
                    title: '{s name=chart/visitors/legend_visits}Total visits{/s}',
                    markerConfig: {
                        type: 'circle',
                        fill: '#0099FF',
                        stroke: '#0099FF'
                    },
                    style: {
                        stroke: '#0099FF',
                        fill: '#0099FF'
                    }
                },
                totalVisitTip
            )
        ];

        me.axes.push({
            type: 'Numeric',
            grid: true,
            position: 'left',
            fields: [
                'desktopImpressions', 'tabletImpressions', 'mobileImpressions', 'totalImpressions',
                'desktopVisits', 'tabletVisits', 'mobileVisits', 'totalVisits'
            ],
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
