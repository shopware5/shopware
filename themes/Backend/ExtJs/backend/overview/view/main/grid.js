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
 * @package    Overview
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/overview/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/overview/view/main/grid"}
Ext.define('Shopware.apps.Overview.view.main.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.overview-main-grid',
    sortableColumns: false,
    features: [{
        ftype: 'summary'
    }],

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * Event will be fired when the date-range changes
             *
             * @event dateChange
             * @param [Date] fromDate
             * @param [Date] toDate
             */
            'dateChange'
        );
    },

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.columns = me.getColumns();
        me.tbar    = me.getToolbar();

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        var columns = [{
            xtype: 'datecolumn',
            header: '{s name=column_date}Date{/s}',
            dataIndex: 'date',
            flex: 1
        }, {
            xtype: 'numbercolumn',
            header: '{s name=column_amount}Turnover{/s}',
            dataIndex: 'amount',
            align: 'right',
            flex: 1,
            summaryType: 'sum',
            summaryRenderer: me.summaryRenderer,
            renderer: me.trendRenderer
        }, {
            header: '{s name=column_countOrders}Orders{/s}',
            dataIndex: 'countOrders',
            align: 'right',
            flex: 1,
            summaryType: 'sum',
            summaryRenderer: me.summaryRenderer,
            renderer: me.trendRenderer
        }, {
            xtype: 'numbercolumn',
            header: '&#216; {s name=column_averageOrders}Order value{/s}',
            dataIndex: 'averageOrders',
            align: 'right',
            flex: 1,
            summaryType: 'average',
            summaryRenderer: me.summaryRenderer,
            renderer: me.trendRenderer
        }, {
            xtype: 'numbercolumn',
            header: '&#216; {s name=column_averageUsers}Visits/orders{/s}',
            dataIndex: 'averageUsers',
            align: 'right',
            flex: 1,
            summaryType: 'average',
            summaryRenderer: me.summaryRenderer,
            renderer: me.trendRenderer
        }, {
            header: '{s name=column_countUsers}New users{/s}',
            dataIndex: 'countUsers',
            align: 'right',
            flex: 1,
            summaryType: 'sum',
            summaryRenderer: me.summaryRenderer,
            renderer: me.trendRenderer
        }, {
            header: '{s name=column_countCustomers}New customers{/s}',
            dataIndex: 'countCustomers',
            align: 'right',
            flex: 1,
            summaryType: 'sum',
            summaryRenderer: me.summaryRenderer,
            renderer: me.trendRenderer
        }, {
            header: '{s name=column_visits}Visits{/s}',
            dataIndex: 'visits',
            flex: 1,
            align: 'right',
            summaryType: 'sum',
            summaryRenderer: me.summaryRenderer,
            renderer: me.trendRenderer
        }, {
            header: '{s name=column_hits}Page impressions{/s}',
            dataIndex: 'hits',
            flex: 1,
            align: 'right',
            summaryType: 'sum',
            summaryRenderer: me.summaryRenderer,
            renderer: me.trendRenderer
        }];

        return columns;
    },

    /**
     * Renders Trendicons
     *
     * @param [object] - value
     * @param [object] - metaData
     * @param [object] - record
     * @param [number] - rowIndex
     * @param [number] - colIndex
     * @param [object] - store
     * @param [object] - view
     * @return [string]
     */
    trendRenderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
        var header = view.getHeaderCt().getHeaderAtIndex(colIndex);
        var colName = header.dataIndex;
        var lastRecord = store.getAt(rowIndex - 1);
        var icon;

        if (header.summaryType === 'average') {
            value = Ext.util.Format.number(value, '0.00');
        }

        if (lastRecord) {
            if (lastRecord.get(colName) < record.get(colName)) {
                icon = 'sprite-arrow-045-small';
            } else if (lastRecord.get(colName) > record.get(colName)) {
                icon = 'sprite-arrow-225-small';
            }

            if (icon) {
                value = '<span class="' + icon +'" style="padding-right: 25px"></span>' + value;
            }
        }

        return value;
    },

    /**
     * Normalizes numbers
     *
     * @param [Object] value - The calculated value.
     * @return [string]
     */
    summaryRenderer: function(value) {
        if (value !== parseInt(value, 10)) {
            value = Ext.util.Format.number(value, '0.00');
        }

        return '<b>' + value + '</b>';
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me       = this,
            today    = new Date();

        var fromDate = Ext.create('Ext.form.field.Date', {
            fieldLabel: '{s name=fieldLabel_from}From{/s}',
            name: 'from_date',
            labelWidth: 50,
            width: 150,
            maxValue: today,
            value: new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())
        });

        var toDate = Ext.create('Ext.form.field.Date', {
            fieldLabel: '{s name=fieldLabel_to}To{/s}',
            name: 'to_date',
            labelWidth: 50,
            width: 150,
            maxValue: today,
            value: today
        });

        var filterButton = Ext.create('Ext.button.Button', {
            text: '{s name=buttonText_filter}Filter{/s}',
            iconCls: 'sprite-filter',
            scope : this,
            handler: function() {
                me.fireEvent('dateChange', fromDate.getValue(), toDate.getValue());
            }
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [ fromDate, toDate, { xtype: 'tbspacer' }, filterButton]
        });

        return toolbar;
    }
});
//{/block}
