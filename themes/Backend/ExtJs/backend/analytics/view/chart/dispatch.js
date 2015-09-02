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
 * Analytics Payment Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/dispatch"}
Ext.define('Shopware.apps.Analytics.view.chart.Dispatch', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-dispatch',
    animate: true,
    shadows: true,

    legend: {
        position: 'right'
    },

    initComponent: function () {
        var me = this;

        me.series = [];

        me.axes = [
            {
                type: 'Numeric',
                position: 'bottom',
                fields: me.getAxesFields('turnover'),
                title: '{s name=general/turnover}Turnover{/s}',
                grid: true,
                minimum: 0
            },
            {
                type: 'Category',
                position: 'left',
                fields: ['name'],
                title: '{s name=chart/dispatch/title}Shipping{/s}'
            }
        ];

        this.series = [
            {
                type: 'bar',
                axis: 'bottom',
                gutter: 80,
                xField: 'name',
                yField: me.getAxesFields('turnover'),
                title: me.getAxesTitles('{s name=general/turnover}Turnover{/s}'),
                stacked: true,
                label: {
                    display: 'insideEnd',
                    field: 'turnover',
                    renderer: Ext.util.Format.numberRenderer('0.00'),
                    orientation: 'horizontal',
                    'text-anchor': 'middle'
                },
                tips: {
                    trackMouse: true,
                    width: 300,
                    height: 60,
                    renderer: function (storeItem, barItem) {
                        var name = storeItem.get('name'),
                            field = barItem.yField,
                            shopId = field.replace('turnover', ''),
                            shop;

                        if (shopId) {
                            shop = me.shopStore.getById(shopId);
                            name = shop.get('name') + '<br><br>&nbsp;' + name;
                        }

                        var turnover = Ext.util.Format.currency(
                            storeItem.get(field),
                            me.subApp.currencySign,
                            2,
                            (me.subApp.currencyAtEnd == 1)
                        );
                        this.setTitle(name + ' : ' + turnover);
                    }
                }
            }
        ];

        me.callParent(arguments);
    }

});
//{/block}
