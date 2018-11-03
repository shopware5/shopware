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
 * Analytics Week Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/week"}
Ext.define('Shopware.apps.Analytics.view.chart.Week', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-week',
    legend: {
        position: 'right'
    },

    initComponent: function () {
        var me = this;

        me.axes = [
            {
                type: 'Time',
                position: 'bottom',
                fields: ['date'],
                title: '{s name=chart/week/titleBottom}Date{/s}',
                dateFormat: '{s name=chart/week/week_format}\\\\C\\\\W{/s} W, Y',
                minorTickSteps: 6,
                step: [Ext.Date.HOUR, 7 * 24],
                label: {
                    rotate: {
                        degrees: 315
                    }
                }
            }
        ];

        me.series = [];

        if (me.shopSelection != Ext.undefined && me.shopSelection.length > 0) {
            Ext.each(me.shopSelection, function (shopId) {
                var shop = me.shopStore.getById(shopId);

                if (!(shop instanceof Ext.data.Model)) {
                    return true;
                }

                me.series.push({
                    type: 'line',
                    title: shop.data.name,
                    axis: ['left', 'bottom'],
                    xField: 'date',
                    yField: 'turnover' + shop.data.id,
                    smooth: true,
                    fill: true,
                    tips: {
                        trackMouse: true,
                        width: 180,
                        highlight: {
                            size: 7,
                            radius: 7
                        },
                        height: 60,
                        renderer: function (storeItem, item) {
                            var value = Ext.util.Format.currency(
                                storeItem.get('turnover' + shop.data.id),
                                me.subApp.currencySign,
                                2,
                                (me.subApp.currencyAtEnd == 1)
                            );
                            this.setTitle(
                                Ext.Date.format(storeItem.get('date'), '{s name=chart/week/week_format}\\\\C\\\\W{/s} W, Y') + '<br><br>&nbsp;' +
                                value
                            );
                        }
                    }
                });
            });
        } else {
            me.series = [
                {
                    type: 'line',
                    axis: ['left', 'bottom'],
                    xField: 'date',
                    yField: 'turnover',
                    fill: true,
                    smooth: true,
                    title: '{s name=general/turnover}Turnover{/s}',
                    tips: {
                        trackMouse: true,
                        width: 180,
                        height: 45,
                        layout: 'fit',
                        items: {
                            xtype: 'container',
                            layout: 'hbox',
                            items: [me.tipChart, me.tipGrid]
                        },
                        renderer: function (storeItem) {
                            var value = Ext.util.Format.currency(
                                storeItem.get('turnover'),
                                me.subApp.currencySign,
                                2,
                                (me.subApp.currencyAtEnd == 1)
                            );
                            this.setTitle(
                                Ext.Date.format(storeItem.get('date'), '{s name=chart/week/week_format}\\\\C\\\\W{/s} W, Y') + '<br><br>&nbsp;' +
                                value
                            )

                        }
                    }
                }
            ];
        }

        me.axes.push({
            type: 'Numeric',
            minimum: 0,
            grid: true,
            position: 'left',
            fields: me.getAxesFields('turnover'),
            title: '{s name=general/turnover}Turnover{/s}'
        });
        me.callParent(arguments);

    }
});
//{/block}
