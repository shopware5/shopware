/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    NewsletterManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/swag_newsletter/main"}

/**
 * Shopware UI - Overview
 * View for existing newsletters
 */
//{block name="backend/newsletter_manager/view/tabs/statistics"}
Ext.define('Shopware.apps.NewsletterManager.view.tabs.Statistics', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.newsletter-manager-tabs-statistics',
    title: '{s name=conversionRates}Conversion rates{/s}',

    layout: {
        type: 'vbox',
        align: 'stretch',
        pack: 'start'
    },

    bodyBorder: 0,
    border: false,
    defaults: {
        bodyBorder: 0
    },

    snippets : {
        columns : {
            date: '{s name=columns/date}Date{/s}',
            subject: '{s name=columns/subject}Subject{/s}',
            recipients: '{s name=columns/recipients}# Recipients{/s}',
            orders: '{s name=columns/orders}# Orders{/s}',
            read: '{s name=columns/read}# read{/s}',
            clicked: '{s name=columns/clicked}# clicked{/s}',
            revenue: '{s name=columns/revenue}Revenue{/s}',
            actions: '{s name=columns/actions}Actions{/s}',
            conversion: '{s name=columns/conversion}Conversion{/s}'
        },
        tooltips: {
            conversion: '{s name=tooltips/conversion}Shows the rate of orders/clicks{/s}',
            read: '{s name=conversion/read}Shows the rate of recipients who read the mail{/s}',
            clicked: '{s name=conversion/clicked}Shows the rate of recipients who opened a link from the mail{/s}'
        }
    },


    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.createPanel();
//        me.dockedItems = [ ];

        me.callParent(arguments);
    },

    /**
     * Creates the panel for the statistics
     * @return Array
     */
    createPanel: function() {
        var me = this;

        return  [
            me.createCharts(),
            me.createGrid()
        ]
    },

    /**
     * Creates the charts component
     * @return Array Charts
     */
    createCharts: function() {

        var me = this,
            chart = Ext.create('Ext.chart.Chart', {
                style: 'background:#fff',
                flex: 2,
                animate: true,
                store: me.store,
                shadow: true,
                theme: 'Category2',
                legend: {
                    position: 'right'
                },
                axes: [{
                    type: 'Numeric',
                    minimum: 0,
                    position: 'left',
                    fields: ['buyRate', 'readRate', 'clickRate', 'conversionRate'],
                    title: '{s name=newsletter/chart/convPercentage}Conversions %{/s}',
                    minorTickSteps: 1,
                    grid: {
                        odd: {
                            opacity: 1,
                            fill: '#ddd',
                            stroke: '#bbb',
                            'stroke-width': 0.5
                        }
                    }
                }, {
                    type: 'Category',
                    position: 'bottom',
                    fields: ['date'],
                    title: '{s name=newsletter/chart/Date}Date{/s}',
                    label: {
                        renderer: function(date){
                            return Ext.util.Format.date(date);
                        },
                        rotate: { degrees: 315 }
                    }

                }],
                series: [{
                    type: 'line',
                    highlight: {
                        size: 7,
                        radius: 7
                    },
                    axis: 'left',
                    smooth: true,
                    fill: false,
                    xField: 'date',
                    yField: 'buyRate',
                    title: '{s name=newsletter/chart/buyRate}Buy Rate{/s}',
                    tips: {
                      trackMouse: true,
                      width: 140,
                      renderer: function(storeItem, item) {
                        this.setTitle('Buy rate: ' + storeItem.get('buyRate') + '%');
                      }
                    },
                    markerConfig: {
                        type: 'triangle',
                        size: 7,
                        radius: 4,
                        'stroke-width': 0
                    }
                }, {
                    type: 'line',
                    highlight: {
                        size: 7,
                        radius: 7
                    },
                    axis: 'left',
                    xField: 'date',
                    yField: 'readRate',
                    title: '{s name=newsletter/chart/readRate}Read Rate{/s}',
                    tips: {
                      trackMouse: true,
                      width: 140,
                      renderer: function(storeItem, item) {
                          this.setTitle('Read rate: ' + storeItem.get('readRate') + '%');
                      }
                    },
                    markerConfig: {
                        type: 'cross',
                        size: 4,
                        radius: 4,
                        'stroke-width': 0
                    }
                }, {
                    type: 'line',
                    highlight: {
                        size: 7,
                        radius: 7
                    },
                    axis: 'left',
                    smooth: true,
                    xField: 'date',
                    yField: 'conversionRate',
                    title: '{s name=newsletter/chart/clickToOrder}Click to Order Rate{/s}',
                    tips: {
                      trackMouse: true,
                      width: 140,
                      renderer: function(storeItem, item) {
                          this.setTitle('Conversion rate: ' + storeItem.get('conversionRate') + '%');
                      }
                    },
                    markerConfig: {
                        type: 'square',
                        size: 4,
                        radius: 4,
                        'stroke-width': 0
                    }
                }, {
                    type: 'line',
                    highlight: {
                        size: 7,
                        radius: 7
                    },
                    axis: 'left',
                    smooth: true,
                    xField: 'date',
                    yField: 'clickRate',
                    title: '{s name=newsletter/chart/clickRate}Click Rate{/s}',
                    tips: {
                      trackMouse: true,
                      width: 140,
                      renderer: function(storeItem, item) {
                          this.setTitle('Click rate: ' + storeItem.get('clickRate') + '%');
                      }
                    },
                    markerConfig: {
                        type: 'circle',
                        size: 4,
                        radius: 4,
                        'stroke-width': 0
                    }
                }]
            });
        return chart;
    },

    /**
     * Creates the grid which is shown below the pie
     * @return Ext.grid.Panel
     */
    createGrid: function() {
        var me = this;

        var grid = Ext.create('Ext.grid.Panel', {
            store: me.store,
            flex: 1,
            columns : me.getColumns(),
            bbar :me.getPagingbar()
        });

        return grid;
    },

    /**
     * Creates the grid columns
     *
     * @return Array columns
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.subject,
                dataIndex: 'mailing.subject',
                flex: 2,
                renderer: function(value, metaData, record) {
                    return '<strong>' + record.get('subject') + '</strong>';
                }
            },
            {
                header: me.snippets.columns.date,
                dataIndex: 'mailing.date',
                renderer: function(value, metaData, record) {
                    return Ext.util.Format.date(record.get('date'));
                },
                flex: 1
            },
            {
                header: me.snippets.columns.recipients,
                dataIndex: 'mailing.recipients',
                renderer: function(value, metaData, record) {
                    return record.get('recipients');
                },
                flex: 1
            },
            {
                header: me.snippets.columns.revenue,
                dataIndex: 'revenue',
                flex: 1,
                sortable: false
            },
            {
                header: me.snippets.columns.orders,
                dataIndex: 'orders',
                flex: 1,
                sortable: false
            },
            {
                header: me.createHeader(me.snippets.tooltips.conversion, me.snippets.columns.conversion),
                menuDisabled: true,
                dataIndex: 'conversionRate',
                renderer: function(value, metaData, record) {
                    return Ext.String.format("[0]%", record.get('conversionRate'));
                },
                flex: 1,
                sortable: false
            },
            {
                header: me.createHeader(me.snippets.tooltips.read, me.snippets.columns.read),
                menuDisabled: true,
                dataIndex: 'mailing.read',
                renderer: function(value, metaData, record) {
                    var rate = record.get('readRate'),
                        read = record.get('read');

                    if(rate != 0) { return Ext.String.format("[0] (<i>[1]%)</i>", read, rate) ; }

                    return read;

                },
                flex: 1.5
            },
            {
                header: me.createHeader(me.snippets.tooltips.clicked, me.snippets.columns.clicked),
                menuDisabled: true,
                dataIndex: 'mailing.clicked',
                renderer: function(value, metaData, record) {
                    var rate = record.get('clickRate'),
                        clicked = record.get('clicked');

                    if(rate != 0) { return Ext.String.format("[0] (<i>[1]%)</i>", clicked, rate) ; }

                    return clicked;

                },
                flex: 1.5
            }
        ];
    },


    /**
     * Little helper function to create tooltips for the column headers
     * @param tooltip
     * @param text
     */
    createHeader: function(tooltip, text) {
        var sprite = 'form-help-icon';
        return Ext.String.format('<span style="float:left;" data-qtip="[0]">[1]</span><span data-qtip="[0]" class="sprite-question" style="width: 25px; height: 25px; display: inline-block; float:right;">&nbsp;</span>', tooltip, text);
    },


    /**
     * Creates pagingbar
     *
     * @return Array
     */
    getPagingbar: function() {
        var me = this;

        return [{
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        }];
    }

});
//{/block}