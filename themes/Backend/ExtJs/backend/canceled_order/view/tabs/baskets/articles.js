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
 * Shopware UI - Articles view
 * used for articles in canceled baskets
 */
//{block name="backend/canceled_order/view/tabs/baskets/articles"}
Ext.define('Shopware.apps.CanceledOrder.view.tabs.baskets.Articles', {
    extend: 'Ext.grid.Panel',
    border: false,
    alias: 'widget.canceled-order-tabs-baskets-articles',
    title: '{s name=baskets/Articles}Articles{/s}',

    snippets : {
        columns : {
            ordernumber: '{s name=columns/orderNumber}Ordernumber{/s}',
            article: '{s name=columns/article}Article{/s}',
            number: '{s name=columns/number}Number{/s}'
        },
        tooltip : {
            edit: '{s name=article/edit}Edit{/s}'
        }
    },

    /**
     * Initializes the component, adds columns, pagingBar and Events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.columns = me.getColumns();
        me.bbar = me.getPagingbar();

        // register Event
        me.addEvents( 'openArticle' );

        me.callParent(arguments);
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
                header: me.snippets.columns.ordernumber,
                dataIndex: 'ordernumber',
                flex: 1
            },
            {
                header: me.snippets.columns.article,
                dataIndex: 'article',
                flex: 1
            },
            {
                header: me.snippets.columns.number,
                dataIndex: 'number',
                flex: 1
            },
            {
                xtype : 'actioncolumn',
                width : 60,
                items : me.getActionColumn()
            }
        ]
    },

    /**
     * Returns an array with the icon for the action column
     *
     * @return Array of buttons
     */
    getActionColumn : function() {
        var me = this;

        return [
            {
                iconCls:'sprite-inbox',
                action:'edit',
                tooltip:me.snippets.tooltip.edit,
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('openArticle', record);
                }
            }
        ];
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
