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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order list backend module
 *
 * The order list view displays the data of the list store.
 * One row displays the head data of a order.
 */
//{block name="backend/order/view/list/position"}
Ext.define('Shopware.apps.Order.view.list.Position', {

    /**
     * One or more alternative component names.
     */
    alternateClassName: 'Shopware.order.position.grid',

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-position-grid',

    /**
     * Set css class
     * @string
     */
    cls:Ext.baseCSSPrefix + 'order-position-grid',

    /**
     * Min heighht of the component
     */
    minHeight: 90,
    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll:true,

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        articleNumber: '{s name=column/article_number}Article number{/s}',
        articleName: '{s name=column/article_name}Article name{/s}',
        quantity: '{s name=column/quantity}Quantity{/s}',
        status: '{s name=column/status}Status{/s}',
        total: '{s name=column/total}Total{/s}',
        tax: '{s name=column/tax}Tax{/s}',
        inStock: '{s name=column/in_stock}Stock{/s}',
        price: '{s name=column/price}Price{/s}',
        openArticle: '{s name=column/open_article}Show article{/s}',
        deletePosition: '{s name=column/delete_position}Delete order position{/s}'
    },

    viewConfig: {
        enableTextSelection: true
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.registerEvents();
        me.columns = me.getColumns();
        me.callParent(arguments);
    },

    /**
     * Registers the component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "open article" action column icon
             * which is placed in the order position list in the options column
             *
             * @event
             * @param [Ext.data.Model] - The record of the order position model
             */
            'openArticle',

            /**
             * Event will be fired when the user clicks the "delete position" action column icon
             * which is placed in the order position list in the options column
             *
             * @event
             * @param [Ext.data.Model] - The record of the order position model
             * @param [Ext.grid.Panel] - The position grid
             */
            'deletePosition'
        );
    },



    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;

        var columns = [
            {
                header: me.snippets.articleNumber,
                dataIndex: 'articleNumber',
                flex:2
            },
            {
                header: me.snippets.articleName,
                dataIndex: 'articleName',
                flex:2
            },
            {
                header: me.snippets.quantity,
                dataIndex: 'quantity',
                flex:1
            },
            {
                header: me.snippets.price,
                dataIndex: 'price',
                flex:1,
                renderer: me.priceColumn
            },
            {
                header: me.snippets.total,
                dataIndex: 'total',
                flex:1,
                renderer: me.totalColumn
            },
            {
                header: me.snippets.status,
                dataIndex: 'statusId',
                flex: 2,
                renderer: me.statusRenderer
            },
            {
                header: me.snippets.tax,
                dataIndex: 'taxId',
                flex:1,
                renderer: me.taxRenderer
            },
            {
                header: me.snippets.inStock,
                dataIndex: 'inStock',
                flex:1
            },
            {
                /**
                 * Special column type which provides
                 * clickable icons in each row
                 */
                xtype:'actioncolumn',
                width:90,
                items:[
                    /*{if {acl_is_allowed privilege=update}}*/
                        {
                            iconCls:'sprite-minus-circle-frame',
                            action:'deletePosition',
                            tooltip: me.snippets.deletePosition,
                            /**
                             * Add button handler to fire the deleteOrder event which is handled
                             * in the list controller.
                             */
                            handler:function (view, rowIndex, colIndex, item) {
                                var store = view.getStore(),
                                    record = store.getAt(rowIndex);

                                me.fireEvent('deletePosition', record, store);
                            }
                        },
                    /*{/if}*/
                    {
                        iconCls:'sprite-inbox',
                        action:'openArticle',
                        tooltip: me.snippets.openArticle,
                        /**
                         * Add button handler to fire the openCustomer event which is handled
                         * in the list controller.
                         */
                        handler:function (view, rowIndex, colIndex, item) {
                            var store = view.getStore(),
                                record = store.getAt(rowIndex);

                            me.fireEvent('openArticle', record);
                        },
                        getClass: function(value, metadata, record) {
                             if (!record.get('articleId') || record.get('mode') !== 0)  {
                                 return 'x-hidden';
                             }
                         }
                    }
                ]
            }
        ];

        return columns;
    },

    taxRenderer: function(value, metaData, rowRecord){
        var me = this,
            record;

        // SW-3289 If we have no valid taxId, return the taxRate
        if(value == 0 || value == null || value == Ext.undefined) {
            return rowRecord.get('taxRate').toString().replace(/[.,]/, Ext.util.Format.decimalSeparator)+'%';
        }

        record = me.taxStore.findRecord('id', value);
        if(record instanceof Ext.data.Model && record.get('tax') != Ext.undefined && record.get('tax') != null) {
            var tax = record.get('tax')+'%';
            return tax.replace(/[.,]/, Ext.util.Format.decimalSeparator);
        }

        return value;
    },

    statusRenderer: function(value) {
        var me = this,
            record;

        record = me.statusStore.getAt(value);
        if(record !== Ext.undefined) {
            return record.get('description');
        }else{
            return value;
        }
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
    },

    /**
     *
     * @param value
     * @return string
     */
    totalColumn: function(value) {
        if ( value === Ext.undefined ) {
            return value;
        }
        return Ext.util.Format.currency(value);
    }



});
//{/block}
