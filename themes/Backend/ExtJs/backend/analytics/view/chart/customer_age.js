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
 * Analytics CustomerAge Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/customer_age"}
Ext.define('Shopware.apps.Analytics.view.chart.CustomerAge', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-customer_age',
    legend: {
        position: 'right'
    },

    initComponent: function () {
        var me = this;

        me.axes = [
            {
                type: 'Numeric',
                minimum: 0,
                grid: true,
                position: 'bottom',
                fields: ['age'],
                title: '{s name=chart/customer_age/age/title}Age{/s}'
            },
            {
                type: 'Numeric',
                position: 'left',
                fields: me.getAxesFields('percent'),
                title: '{s name=chart/customer_age/percent/title}Percentage{/s}'
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
                            xField: 'age',
                            yField: 'percent' + shopId
                        },
                        {
                            width: 180,
                            height: 60,
                            renderer: function (storeItem) {
                                this.setTitle(
                                    shop.get('name') + '<br><br>&nbsp;' +
                                    '{s name=chart/customer_age/age/title}Age{/s}: ' + storeItem.get('age') + '<br>&nbsp;' +
                                    '{s name=chart/customer_age/percent/title}Percentage{/s}: ' + storeItem.get('percent' + shopId) + ' %'
                                );
                            }
                        }
                    )
                );
            });
        } else {
            me.series = [
                me.createLineSeries(
                    {
                        xField: 'age',
                        yField: 'percent',
                        title: '{s name=chart/customer_age/percent/title}Percentage{/s}'
                    },
                    {
                        width: 180,
                        height: 45,
                        renderer: function (storeItem) {
                            this.setTitle(
                                '{s name=chart/customer_age/age/title}Age{/s}: ' + storeItem.get('age') + '<br><br>&nbsp;' +
                                '{s name=chart/customer_age/percent/title}Percentage{/s}: ' + storeItem.get('percent') + ' %'
                            );
                        }
                    }
                )
            ];
        }


        me.callParent(arguments);
    }
});
//{/block}
