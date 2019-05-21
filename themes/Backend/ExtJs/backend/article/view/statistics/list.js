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
//{block name="backend/article/view/statistics/list"}
Ext.define('Shopware.apps.Article.view.statistics.List', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-statistics-list',

    /**
     * Set css class
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-statistics-list',

    sortableColumns: false,
    features: [{
        ftype: 'summary'
    }],

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title: '{s name=statistic/title}Overview{/s}',
        columns:{
            date: '{s name=statistic/list/column/date}Date{/s}',
            revenue: '{s name=statistic/list/column/revenue}Revenue{/s}',
            orders: '{s name=statistic/list/column/orders}Orders{/s}'
        },
        toolbar:{
            from:'{s name=statistic/list/toolbar/from}From{/s}',
            to:'{s name=statistic/list/toolbar/to}To{/s}',
            filter:'{s name=statistic/list/toolbar/filter}Filter{/s}'
        }
    },

    /**
     * Initialize the Shopware.apps.Article.view.statistics.List and defines the necessary default configuration
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.title = me.snippets.title;

        me.registerEvents();

        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.dockedItems = [ me.toolbar, me.pagingbar ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
                /**
                 * Event will be fired when the user clicks the delete button in the toolbar or
                 * use the action column of the grid to remove one or multiple statistics
                 * @event deleteEsd
                 * @param [array] Record - The selected records
                 */
                'dateChange'
        );
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [
            {
                header: me.snippets.columns.date,
                xtype: 'datecolumn',
                dataIndex: 'date',
                flex: 1
            },
            {
                header: me.snippets.columns.revenue,
                dataIndex: 'revenue',
                summaryType: 'sum',
                summaryRenderer: me.summaryRenderer,
                align: 'right',
                flex: 1
            },
            {
                header: me.snippets.columns.orders,
                dataIndex: 'orders',
                summaryType: 'sum',
                summaryRenderer: me.summaryRenderer,
                align: 'right',
                flex: 1
            }
        ];
    },

    /**
     * Creates the grid toolbar with the different buttons.
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function () {
        var me   = this,
        theFirst = new Date(),
        today    = new Date();

        theFirst.setDate(1);

        me.fromDate = Ext.create('Ext.form.field.Date', {
            fieldLabel: me.snippets.toolbar.from,
            name: 'from_date',
            maxValue: today,
            value: theFirst
        });

        me.toDate = Ext.create('Ext.form.field.Date', {
            fieldLabel: me.snippets.toolbar.to,
            name: 'to_date',
            maxValue: today,
            value: today
        });

        var filterButton = Ext.create('Ext.button.Button', {
            text: me.snippets.toolbar.filter,
            cls: 'small secondary',
            scope : this,
            handler: function() {
                me.fireEvent('dateChange', me.fromDate.getValue(), me.toDate.getValue());
            }
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: [ me.fromDate, me.toDate, filterButton]
        });

        return toolbar;
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
    }
});
//{/block}
