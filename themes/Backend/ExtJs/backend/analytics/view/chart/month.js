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
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/month"}
Ext.define('Shopware.apps.Analytics.view.chart.Month', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-month',
    legend: {
        position: 'right'
    },


    initComponent: function () {
        var me = this;

        me.axes = [
            {
                type: 'Time',
                position: 'bottom',
                fields: ['normal'],
                title: '{s name=chart/month/titleBottom}Month{/s}',
                step:[ Ext.Date.MONTH, 1 ],
                label: {
                    renderer:function (value) {
                        var myDate = Ext.Date.add(new Date(value), Ext.Date.DAY, 4);
                        return Ext.util.Format.date(myDate, 'M, Y');
                    },
                    rotate: {
                        degrees: 315
                    }
                }
            }
        ];

        me.series = [];

        if (me.shopSelection != Ext.undefined && me.shopSelection.length > 0) {
            me.series = me.getSeriesForShopSelection();
        } else {
            me.series = [
                me.createLineSeries(
                    {
                        xField: 'normal',
                        yField: 'turnover',
                        title: '{s name=general/turnover}Turnover{/s}'
                    },
                    {
                        width: 180,
                        height: 45,
                        renderer: function (storeItem) {
                            var value = Ext.util.Format.currency(
                                storeItem.get('turnover'),
                                me.subApp.currencySign,
                                2,
                                (me.subApp.currencyAtEnd == 1)
                            );

                            this.setTitle(Ext.Date.format(storeItem.get('normal'), 'F, Y') + '<br><br>&nbsp;' + value);
                        }
                    }
                )
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
    },

    getSeriesForShopSelection: function() {
        var me = this,
            series = [];

        Ext.each(me.shopSelection, function (shopId) {
            var shop = me.shopStore.getById(shopId);

            if (!(shop instanceof Ext.data.Model)) {
                return true;
            }

            series.push(
                me.createLineSeries(
                    {
                        title: shop.get('name'),
                        xField: 'normal',
                        yField: 'turnover' + shopId
                    },
                    {
                        width: 180,
                        height: 45,
                        renderer: function (storeItem) {
                            me.renderShopData(storeItem, this, shop);
                        }
                    }
                )
            );

        });

        return series;
    },

    renderShopData: function(storeItem, tip, shop) {
        var me = this;

        var sales = Ext.util.Format.currency(
            storeItem.get('turnover' + shop.get('id')),
            me.subApp.currencySign,
            2,
            (me.subApp.currencyAtEnd == 1)
        );

        tip.setTitle(Ext.Date.format(storeItem.get('normal'), 'F, Y') + '<br><br>&nbsp;' + sales);
    }


});
//{/block}
