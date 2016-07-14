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
 * Analytics Supplier Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/supplier"}
Ext.define('Shopware.apps.Analytics.view.chart.Supplier', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-supplier',
    animate: true,
    shadows: true,

    initComponent: function () {
        var me = this;

        this.series = [
            {
                type: 'pie',
                field: 'turnover',
                showInLegend: true,
                label: {
                    title: '{s name=chart/supplier/title}Supplier{/s}',
                    field: 'name',
                    display: 'rotate',
                    contrast: true,
                    font: '18px Arial'
                },
                tips: {
                    trackMouse: true,
                    width: 300,
                    height: 45,
                    renderer: function (storeItem) {
                        var value = Ext.util.Format.currency(
                            storeItem.get('turnover'),
                            me.subApp.currencySign,
                            2,
                            (me.subApp.currencyAtEnd == 1)
                        );

                        this.setTitle(
                            storeItem.get('name') + '<br><br>&nbsp;' +
                            value
                        );
                    }
                }
            }
        ];
        me.callParent(arguments);
    }
});
//{/block}
