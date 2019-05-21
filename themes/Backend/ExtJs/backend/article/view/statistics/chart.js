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
 *
 * @category   Shopware
 * @package    Article
 * @subpackage Esd
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article esd page
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/esd/chart"}
Ext.define('Shopware.apps.Article.view.statistics.Chart', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.chart.Chart',

    /**
     * Chart of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-statistics-chart',

    /**
     * Set css class
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-statistics-chart',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        revenue: '{s name=statistic/list/revenue}Revenue:{/s}',
        orders: '{s name=statistic/list/orders}Orders:{/s}'
    },

    insetPadding: 20,
    theme: 'Green',

    /**
     * Initialize the Shopware.apps.Article.view.statistics.Chart and defines the necessary default configuration
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.axes = [{
            type: 'Numeric',
            position: 'left',
            fields: [ 'revenue'],
            grid: true,
            minimum: 0
        }, {
            type: 'Category',
            position: 'bottom',
            fields: ['groupdate'],
            setLabels: function() {
                var store = this.chart.getChartStore();
                var labels = this.labels = [];
                var monthArray = Ext.Array.map(Ext.Date.monthNames, function (e) { return [e]; });
                store.each(function(item) {
                    labels.push(monthArray[item.get('month')-1]);
                });
            }
        }];

        me.series = [{
            type: 'line',
            axis: 'left',
            highlight: true,
            tips: {
            trackMouse: true,
                width: 120,
                renderer: function(storeItem, item) {
                    this.setTitle(me.snippets.orders + ' ' + storeItem.get('orders') + '<br>' + me.snippets.revenue + ' ' +  storeItem.get('revenue'));
                }
            },
            markerConfig: {
                type: 'circle',
                fill: '#13be7b',
                size: 4,
                radius: 4,
                'stroke-width': 0
            },
            xField: 'groupdate',
            yField: 'revenue'
        }];

        me.callParent(arguments);
    }
});
//{/block}
