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
 * Shopware UI - Statistics
 * View for the statistics tab
 */
//{block name="backend/canceled_order/view/tabs/statistics"}
Ext.define('Shopware.apps.CanceledOrder.view.tabs.Statistics', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.canceled-order-tabs-statistics',
    title: '{s name=statistics}Statistics{/s}',

    layout: {
        type: 'vbox',
        align: 'stretch',
        pack: 'start'
    },

    snippets: {
        date: {
            from: '{s name=date/from}From{/s}',
            to: '{s name=date/to}To{/s}'
        },
        columns: {
            payment: '{s name=columns/payment}Payment{/s}',
            totalAmount: '{s name=columns/numberOfPayments}Number{/s}'
        }
    },


    /**
     * Initializes the component, adds panel and tool
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.createPanel();
        me.dockedItems = me.getToolbar();

        me.callParent(arguments);
    },

    /**
     * Creates the toolbar for the statistics tab and removes the searchField
     * @return Ext.toolbar.Toolbar
     */
    getToolbar: function() {
        var me = this;

        var toolbar = Ext.create('widget.canceled-order-toolbar');
        // remove search field from the statistics toolbar
        toolbar.items.items.pop();
        return toolbar;
    },

    /**
     * Creates the panel for the statistics
     * @return Array
     */
    createPanel: function() {
        var me = this;

        return  [
            me.createPie(),
            me.createGrid()
        ]
    },

    /**
     * Creates the grid which is shown below the pie
     * @return Ext.grid.Panel
     */
    createGrid: function() {
        var me = this;

        var grid = Ext.create('Ext.grid.Panel', {
            flex: 30,
            store: me.store,
            columns : me.getColumns()
        });

        return grid;
    },


    /**
     * Creates the grid columns
     *
     * @return grid columns
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.payment,
                dataIndex: 'paymentName',
                width: '50%'
            },
            {
                header: me.snippets.columns.totalAmount,
                dataIndex: 'number',
                width: '50%'
            }
        ]
    },

    /**
     * Formats currency.
     * If value is neither string nor number, value will be returned as is
     *
     * @param value
     * @return mixed
     */
    amountRenderer: function(value, metaData, record) {
        if (!record) {
            return '';
        }
        value = record.get('paymentValue');

        if(Ext.isNumber(value) || Ext.isString(value)) {
            return Ext.util.Format.currency(value);
        }

        return value
    },

    /**
     * Creates the Pie chart for the statistics tab
     * @return Ext.chart.Chart
     */
    createPie: function() {
        var me = this;

        var pie = Ext.create('Ext.chart.Chart', {
            flex: 70,
            animate: true,
            store: me.store,
            theme: 'Base:gradients',
            series: [{
                type: 'pie',
                angleField: 'number',
                showInLegend: true,
                tips: {
                    trackMouse: true,
                    width: 140,
                    height: 28,
                    renderer: function(storeItem, item) {
                        // calculate and display percentage on hover
                        var total = 0;
                        me.store.each(function(rec) {
                            total += rec.get('number');
                        });
                        this.setTitle(storeItem.get('paymentName') + ': ' + Math.round(storeItem.get('number') / total * 100) + '%');
                    }
                },
                highlight: {
                    segment: {
                        margin: 20
                    }
                },
                label: {
                    field: 'paymentName',
                    display: 'rotate',
                    contrast: true,
                    font: '18px Arial'
                }
            }]
        });

        return pie;
    }
});
//{/block}
