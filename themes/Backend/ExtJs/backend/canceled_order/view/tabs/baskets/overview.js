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
 * @package    CanceledOrder
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/canceled_order/view/main}

/**
 * Shopware UI - Canceled baskets
 * This tab holds a grid displaying canceled baskets
 */
//{block name="backend/canceled_order/view/tabs/baskets/overview"}
Ext.define('Shopware.apps.CanceledOrder.view.tabs.baskets.Overview', {
    extend: 'Ext.grid.Panel',
    border: false,
    alias: 'widget.canceled-order-tabs-baskets-overview',
    title: '{s name=baskets/Overview}Overview{/s}',

    snippets : {
        columns : {
            time: '{s name=columns/date}Date{/s}',
            price: '{s name=columns/amount}Total amount{/s}',
            average: '{s name=columns/averageItemValue}Ø Price per unit{/s}',
            number: '{s name=columns/number}Number of Baskets{/s}'
        },
        days: '{s name=days}Days{/s}',
        day: '{s name=day}Day{/s}'
    },

    /**
     * Initializes the component, adds groupingFeature, Columns and pagingBar
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.columns = me.getColumns();
        me.bbar = me.getPagingbar();

        me.features = [ me.createGroupingFeature() ];
        me.callParent(arguments);
    },

    /**
     * create the grouping feature for the grid
     * @return Ext.grid.feature.GroupingSummary
     */
    createGroupingFeature: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.GroupingSummary', {
            groupHeaderTpl: Ext.create('Ext.XTemplate',
                '<span>{ name:this.formatHeader }</span>',
                '<span>&nbsp;({ rows.length } ' + me.snippets.days + ')</span>',
                {
                    formatHeader: function(field) {
                        var date = new Date(field);
                        return Ext.Date.format(date, 'F Y');
                    }
                }
            )
        });
    },

    /**
     * Creates the grid columns
     * Data indices where chosen in order to match the database scheme for sorting in the PHP backend.
     * Therefore each Column requieres its own renderer in order to display the correct value.
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.time,
                dataIndex: 'basket.date',
                flex: 1,
                renderer: me.timeRenderer,
                // Summary count not necessary as baskets are already counted in the grouping header
                summaryType: 'count',
                summaryRenderer: function(value, summaryData, dataIndex) {
                    return ((value === 0 || value > 1) ? '<b>(' + value + ' ' + me.snippets.days + ')</b>' : '<b>(1 ' + me.snippets.day + ')</b>');
                }
            },
            {
                header: me.snippets.columns.price,
                dataIndex: 'basket.price',
                flex: 1,
                renderer: me.priceRenderer,
                // We do need a custom summaryType here, as the dataIndex 'basket.price' will not work properly
                // This way the proper record is extracted
                summaryType: function(records){
                    var i = 0,
                            length = records.length,
                            total = 0,
                            record;
                    for (; i < length; i++){
                        record = records[i];
                        total += record.get('price');
                    }
                    return total;
                },
                summaryRenderer: function(value, summaryData, dataIndex) {
                    if(Ext.isNumber(value) || Ext.isString(value)) {
                        return '<b>' + Ext.util.Format.currency(value)+ '</b>';
                    }

                    return value
                }
            },
            {
                header: me.snippets.columns.average,
                dataIndex: 'average',
                flex: 1,
                summaryType: 'average',
                renderer: function(value) {
                    return Ext.util.Format.currency(value);
                },
                summaryRenderer: function(value) {
                    return '<b>' + Ext.util.Format.currency(value) + '</b>';
                }
            },
            {
                header: me.snippets.columns.number,
                dataIndex: 'number',
                flex: 1,
                summaryType: 'sum',
                summaryRenderer: function(value) {
                    return '<b>' + value + '</b>';
                }

            }
        ]
    },

    /**
     * Render and format price
     * @param value
     * @param metaData
     * @param record
     * @return mixed
     */
    priceRenderer: function(value, metaData, record){
        if (!record) {
            return '';
        }
        value = record.get('price');

        if(Ext.isNumber(value) || Ext.isString(value)) {
            return Ext.util.Format.currency(value);
        }

        return value
    },

    /**
     * Returns date from record. Needed because the column's dataIndices
     * are named in order to match the database
     * @param value
     * @param metaData
     * @param record
     * @return
     */
    timeRenderer: function(value, metaData, record) {
        return Ext.util.Format.date(record.get('date'));
    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
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
