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
 * Analytics CustomerGroup Chart
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/customer_group"}
Ext.define('Shopware.apps.Analytics.view.chart.CustomerGroup', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-customer-group',

    legend: {
        position: 'right'
    },
    mask: 'horizontal',

    initComponent: function () {
        var me = this;

        me.series = [
            {
                type: 'pie',
                field: 'turnover',
                showInLegend: true,
                label: {
                    field: 'customerGroup',
                    display: 'rotate',
                    contrast: true,
                    font: '18px Arial'
                },
                tips: {
                    trackMouse: true,
                    width: 180,
                    height: 30,
                    renderer: function (storeItem) {
                        var value = Ext.util.Format.currency(
                            storeItem.get('turnover'),
                            me.subApp.currencySign,
                            2,
                            (me.subApp.currencyAtEnd == 1)
                        );

                        var title = '{s name=general/turnover}Turnover{/s}: ' + value;
                        this.setTitle(title);
                    }
                }
            }
        ];

        me.callParent(arguments);
    }
});
//{/block}
