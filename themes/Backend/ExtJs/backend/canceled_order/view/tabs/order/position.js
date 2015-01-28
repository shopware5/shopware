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

//{namespace name=backend/canceled_order/view/main}

/**
 * Shopware UI - Position view for the order tab, allows the user to see the order positions
 */
//{block name="backend/canceled_order/view/tabs/order/position"}
Ext.define('Shopware.apps.CanceledOrder.view.tabs.order.Position', {
    extend: 'Ext.grid.Panel',
    title: '{s name=position/title}Order positions{/s}',
    region: 'south',
    height: 300,
    collapsed: false,
    collapsible: true,
    alias: 'widget.canceled-order-view-order-position',

    snippets : {
        columns : {
            articleNumber: '{s name=columns/position/number}Articlenumber{/s}',
            articleName: '{s name=columns/position/name}Articlename{/s}',
            articleQuantity: '{s name=columns/position/quantity}Quantity{/s}',
            articlePrice: '{s name=columns/position/price}Price{/s}',
            totalAmount: '{s name=columns/position/total_amount}Total Amount{/s}',
            openArticle: '{s name=column/position/open_article}Show article{/s}'

        }
    },

    /**
     * Init the main detail component, add components
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.columns = me.getColumns();
        // register events
        me.addEvents( 'openArticle' );
        me.callParent(arguments);
    },

    /**
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
                header: me.snippets.columns.articleNumber,
                dataIndex: 'articleNumber',
                flex: 1
            },
            {
                header: me.snippets.columns.articleName,
                dataIndex: 'articleName',
                flex: 1
            },
            {
                header: me.snippets.columns.articleQuantity,
                dataIndex: 'quantity',
                flex: 1
            },
            {
                header: me.snippets.columns.articlePrice,
                dataIndex: 'price',
                flex: 2,
                renderer: me.priceColumn

            },
            {
                header: me.snippets.columns.totalAmount,
                dataIndex: 'total',
                flex: 1,
                renderer: me.priceColumn
            },
            {
                xtype:'actioncolumn',
                width:90,
                items:[
                    {
                        iconCls:'sprite-inbox',
                        action:'openArticle',
                        tooltip: me.snippets.openArticle,
                        handler:function (view, rowIndex) {
                            var store = view.getStore(),
                                record = store.getAt(rowIndex);

                            me.fireEvent('openArticle', record);
                        },
                        getClass: function(value, metadata, record) {
                            if (!record.get('articleId') || record.get('mode') != '0') {
                                return 'x-hidden';
                            }
                        }
                    }
                ]
            }
        ];
    },

    /**
     * Formats the price column
     * @param [string] - The price value
     * @return [string] - The passed value, formatted with Ext.util.Format.currency()
     */
    priceColumn:function (value) {
        if ( value === Ext.undefined ) {
            return value;
        }
        return Ext.util.Format.currency(value);
    }
});
//{/block}
