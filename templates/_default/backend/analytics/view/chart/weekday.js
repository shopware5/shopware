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
 * Analytics Weekday Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * todo@all - documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/weekday"}
Ext.define('Shopware.apps.Analytics.view.chart.Weekday', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-weekday',
    legend: {
        position: 'right'
    },
    animate: {
        easing: 'bounceOut',
        duration: 750
    },

    axes: [
        {
            type: 'Numeric',
            minimum: 0,
            position: 'left',
            fields: ['amount'],
            title: '{s name=chart/weekday/titleLeft}Sales{/s}'
        },
        {
            type: 'category',
            position: 'bottom',
            fields: ['displayDate'],
            title: '{s name=chart/weekday/titleBottom}Weekday{/s}'
        }
    ],

    initComponent: function () {
        var me = this;

        me.series = [];

        // Initiate stores for handling multiple shop values
        this.initMultipleShopTipsStores();

        if (me.shopSelection != Ext.undefined && me.shopSelection.length > 0) {
            Ext.each(me.shopSelection, function (shopId) {
                var shop = me.shopStore.getById(shopId);

                if (!(shop instanceof Ext.data.Model)) {
                    return true;
                }
                me.series.push({
                    type: 'line',
                    title: shop.data.name,
                    axis: ['left'],
                    xField: 'displayDate',
                    yField: 'amount' + shopId,
                    smooth: true,
                    tips: {
                        trackMouse: true,
                        width: 120,
                        highlight: {
                            size: 7,
                            radius: 7
                        },
                        height: 60,
                        renderer: function (storeItem, item) {
                            this.setTitle(storeItem.get('displayDate'));
                            var sales = Ext.util.Format.currency(storeItem.get('amount' + shopId), shop.data.currencyChar);
                            this.update(sales);
                        }
                    }
                })
            });
        } else {
            me.series = [
                {
                    type: 'column',
                    axis: 'left',
                    xField: 'displayDate',
                    style: {
                        fill: 'url(#bar-gradient)',
                        'stroke-width': 3
                    },
                    markerConfig: {
                        type: 'circle',
                        size: 4,
                        radius: 4,
                        'stroke-width': 0,
                        fill: '#38B8BF',
                        stroke: '#38B8BF'
                    },
                    title: 'Total sales',
                    yField: 'amount',
                    tips: {
                        trackMouse: true,
                        width: 580,
                        height: 130,
                        layout: 'fit',
                        items: {
                            xtype: 'container',
                            layout: 'hbox',
                            items: [me.tipChart, me.tipGrid]
                        },
                        renderer: function (cls, item) {
                            me.initMultipleShopTipsData(item, this, 'l', '{s name=chart/weekday/legendSalesOn}Sales on{/s}');
                        }
                    }
                }
            ];
        }

        me.callParent(arguments);
    }
});
//{/block}