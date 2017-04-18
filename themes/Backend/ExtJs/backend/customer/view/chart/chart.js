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
 * @package    Customer
 * @subpackage Chart
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/chart/chart"}
Ext.define('Shopware.apps.Customer.view.chart.Chart', {

    extend: 'Ext.chart.Chart',

    shadow: true,
    margin: 30,
    legend: true,
    animate: true,

    initComponent: function () {
        var me = this;

        me.series = me.createSeries();

        me.axes = me.createAxes();

        me.callParent(arguments);
    },

    getAxesFields: function () {
        var me = this,
            fields = [];

        Ext.each(me.getFields(), function(item) {
            fields.push(item.name);
        });
        return fields;
    },

    getFields: function () {
        return [];
    },

    createAxes: function () {
        var me = this;
        return [{
            type: 'Numeric',
            position: 'left',
            fields: me.getAxesFields(),
            label: {
                renderer: Ext.util.Format.numberRenderer('0,0')
            },
            title: '{s name="window/amount"}Amout{/s}',
            grid: true,
            minimum: 0
        }, {
            type: 'Category',
            position: 'bottom',
            title: '{s name="chart_month"}Month{/s}',
            fields: ['yearMonth']
        }];
    },

    createSeries: function () {
        var me = this,
            series = [];

        Ext.each(me.getFields(), function(item) {
            if (item.hasOwnProperty('title')) {
                series.push(me.createLineSeries(item.name, item.title));
            } else {
                series.push(me.createLineSeries(item.name, item.name));
            }
        });

        return series;
    },

    createLineSeries: function(field, title) {
        return {
            type: 'line',
            highlight: { size: 7, radius: 7 },
            axis: 'left',
            fill: true,
            title: title,
            smooth: true,
            xField: 'yearMonth',
            yField: field,
            markerConfig: { type: 'circle', size: 4, radius: 4, 'stroke-width': 0 }
        };
    }
});
// {/block}
