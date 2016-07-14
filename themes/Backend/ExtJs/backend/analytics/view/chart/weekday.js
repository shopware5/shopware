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
 * Analytics Weekday Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/weekday"}
Ext.define('Shopware.apps.Analytics.view.chart.Weekday', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-weekday',
    legend: {
        position: 'right'
    },

    initComponent: function () {
        var me = this;

        me.axes = [
            {
                type: 'Category',
                position: 'bottom',
                fields: ['date'],
                title: '{s name=chart/weekday/titleBottom}Weekday{/s}',
                label: {
                    renderer:function (value) {
                        return Ext.util.Format.date(value, 'l');
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

                me.series.push(
                    me.createLineSeries(
                        {
                            title: shop.get('name'),
                            xField: 'displayDate',
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
        } else {
            me.series = [
                me.createLineSeries(
                    {
                        xField: 'displayDate',
                        yField: 'turnover',
                        title: '{s name=general/turnover}Turnover{/s}'
                    },
                    {
                        width: 180,
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
            grid: true,
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

        tip.setTitle(Ext.Date.format(storeItem.get('date'), 'l') + '<br><br>&nbsp;' + sales);
    }

});
//{/block}
