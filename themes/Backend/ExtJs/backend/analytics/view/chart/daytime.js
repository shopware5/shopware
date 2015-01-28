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
 * Analytics Time Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/daytime"}
Ext.define('Shopware.apps.Analytics.view.chart.Daytime', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-daytime',
    legend: {
        position: 'right'
    },
    animate: true,


    initComponent: function () {
        var me = this;

        me.series = [];
        me.axes = [
            {
                type: 'Time',
                position: 'bottom',
                fields: ['date'],
                title: '{s name=chart/daytime/titleBottom}Time{/s}',
                step: [Ext.Date.HOUR, 1],
                dateFormat: timeFormat
            }
        ];

        if (me.shopSelection != Ext.undefined && me.shopSelection.length > 0) {
            Ext.each(me.shopSelection, function (shopId) {
                var shop = me.shopStore.getById(shopId);

                if (!(shop instanceof Ext.data.Model)) {
                    return true;
                }

                me.series.push(
                    me.createLineSeries(
                        {
                            title: shop.data.name,
                            xField: 'date',
                            yField: 'turnover' + shopId
                        },
                        {
                            width: 150,
                            height: 45,
                            renderer: function (storeItem) {
                                me.renderShopData(storeItem, this, shop);
                            }
                        }
                    )
                );
            });
        } else {
            me.series = [
                me.createLineSeries(
                    {
                        xField: 'date',
                        yField: 'turnover',
                        title: '{s name=general/turnover}Turnover{/s}'
                    },
                    {
                        width: 150,
                        height: 45,
                        renderer: function (storeItem) {
                            me.renderShopData(storeItem, this, null);
                        }
                    }
                )
            ];
        }

        me.axes.push({
            type: 'Numeric',
            minimum: 0,
            position: 'left',
            fields: me.getAxesFields('turnover'),
            title: '{s name=general/turnover}Turnover{/s}'
        });

        me.callParent(arguments);
    },

    renderShopData: function(storeItem, tip, shop) {
        var me = this,
            field = 'turnover';

        if (shop) {
            field += shop.get('id');
        }

        var sales = Ext.util.Format.currency(
            storeItem.get(field),
            me.subApp.currencySign,
            2,
            (me.subApp.currencyAtEnd == 1)
        );

        var timeValue = storeItem.get('date');
        timeValue.setMinutes(0);
        tip.setTitle(
            Ext.util.Format.date(timeValue, timeFormat)+
            '<br><br>&nbsp;' + sales
        );
    }

});
//{/block}
