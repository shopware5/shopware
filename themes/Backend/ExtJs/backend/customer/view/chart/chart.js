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
    cls: 'customer-stream-chart',
    shadow: true,
    margin: 30,
    legend: true,
    animate: true,
    background: '#fff',

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

    currencyRenderer: function(value) {
        value = value * 1;
        return Ext.util.Format.currency(value, this.getCurrency(), 2, (this.subApp.currencyAtEnd == 1));
    },

    getCurrency: function() {
        var currency = this.subApp.currencySign;

        switch (currency) {
            case '&euro;':
                return '&#8364;';
            case '&pound;':
                return '&#163;';
            default:
                return currency;
        }
    },

    createAxes: function () {
        var me = this;
        return [{
            type: 'Numeric',
            position: 'left',
            fields: me.getAxesFields(),
            title: '{s name="amount_axes"}{/s}',
            grid: true,
            minimum: 0,
            label: {
                renderer: function (value) {
                    return me.currencyRenderer(value);
                }
            }
        }, {
            type: 'Category',
            position: 'bottom',
            title: '{s name="month"}Month{/s}',
            fields: ['yearMonth'],
            label: {
                renderer: function (value) {
                    var myDate = Ext.Date.add(new Date(value), Ext.Date.DAY, 4);
                    return Ext.util.Format.date(myDate, 'M, Y');
                },
                rotate: {
                    degrees: 315
                }
            }
        }];
    },

    createSeries: function () {
        var me = this,
            series = [];

        Ext.each(me.getFields(), function(item) {
            series.push(me.createLineSeries(item.name, item.title, item.currency));
        });

        return series;
    },

    createLineSeries: function(field, title, currency) {
        var me = this;

        return {
            type: 'line',
            axis: 'left',
            highlight: { size: 7, radius: 7 },
            fill: true,
            smooth: true,
            title: title,
            xField: 'yearMonth',
            yField: field,
            tips: {
                trackMouse: true,
                layout: 'fit',
                lineField: field,
                fieldTitle: title,
                height: 45,
                width: 300,
                highlight: { size: 7, radius: 7 },
                renderer: function (storeItem) {
                    var value = storeItem.get(this.lineField);

                    if (currency) {
                        value = me.currencyRenderer(value);
                    }

                    this.setTitle(
                        '<div class="customer-stream-chart-tip">' +
                            '<span class="customer-stream-chart-tip-label">' + this.fieldTitle + ':</span>&nbsp;'+
                            '<span class="customer-stream-chart-tip-amount">' + value + '</span>' +
                        '</div>'
                    );
                }
            },
            markerConfig: { type: 'circle', size: 4, radius: 4, 'stroke-width': 0 }
        };
    }
});
// {/block}
