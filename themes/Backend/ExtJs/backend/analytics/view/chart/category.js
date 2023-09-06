/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

/**
 * Analytics Category Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name="backend/analytics/view/main"}
//{block name="backend/analytics/view/chart/category"}
Ext.define('Shopware.apps.Analytics.view.chart.Category', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-category',

    legend: {
        position: 'right'
    },
    mask: 'horizontal',
    listeners: {
        select: {
            fn: function (me, selection) {
                me.setZoom(selection);
                me.mask.hide();
            }
        }
    },
    initComponent: function () {
        var me = this;

        me.series = [
            {
                type: 'pie',
                field: 'turnover',
                showInLegend: true,
                listeners: {
                    itemmouseup: function (item) {
                        var node = item.storeItem.data.node;
                        if (!node) {
                            return;
                        }
                        me.setLoading(true);
                        me.store.getProxy().extraParams['node'] = node;
                        me.store.load({
                            callback: function() {
                                me.setLoading(false);
                            }
                        });
                    }
                },
                tips: {
                    trackMouse: true,
                    width: 180,
                    height: 45,
                    renderer: function (storeItem) {
                        var value = Ext.util.Format.currency(
                            storeItem.get('turnover'),
                            me.subApp.currencySign,
                            2,
                            (me.subApp.currencyAtEnd == 1)
                        );

                        this.setTitle(storeItem.get('name') + '<br><br>&nbsp;' +  value);
                    }
                },
                label: {
                    field: 'name',
                    display: 'rotate',
                    contrast: true,
                    font: '18px Arial'
                }
            }
        ];

        me.callParent(arguments);
    }
});
//{/block}
