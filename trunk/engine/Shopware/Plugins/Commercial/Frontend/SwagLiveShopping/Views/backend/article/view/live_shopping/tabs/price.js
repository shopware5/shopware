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
 * @package    SwagLiveShopping
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{block name="backend/live_shopping/view/live_shopping/tabs/price"}
//{namespace name="backend/live_shopping/article/view/main"}
Ext.define('Shopware.apps.Article.view.live_shopping.tabs.Price', {

    extend: 'Ext.grid.Panel',

    title: '{s name=prices/title}Prices{/s}',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.live-shopping-price-listing',
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
    initComponent: function() {
        var me = this;
        me.registerEvents();
        me.columns = me.createColumns();
        me.tbar = me.createToolBar();
        me.plugins = [ me.createCellEditor() ];
        me.callParent(arguments);
    },

    /**
     * Adds the specified events to the list of events which this Observable may fire
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * @Event
             * Custom component event.
             * Fired when the customer select a customer group in the toolbar combo box.
             * @param Ext.data.Model The selected record
             */
            'addPrice',

            /**
             * @Event
             * Custom component event.
             * Fired when the user clicks the delete action column item.
             * @param Ext.data.Model The row record
             */
            'deletePrice',

            /**
             * @Event
             * Custom component event.
             * Fired after the user change a live shopping price over the cell editor.
             * @param Ext.data.Model The row record
             */
            'changePrice'
        );
    },


    /**
     * Creates the columns for the grid panel.
     * @return Array
     */
    createColumns: function() {
        var me = this, columns = [];

        columns.push(me.createNameColumn());
        columns.push(me.createPriceColumn());
        columns.push(me.createEndPriceColumn());
        columns.push(me.createPerMinuteColumn());
        columns.push(me.createActionColumn());

        return columns;
    },

    /**
     * Creates the name column for the listing.
     * @return Ext.grid.column.Column
     */
    createNameColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=prices/customer_group_column}Customergroup{/s}',
            dataIndex: 'customerGroup.name',
            flex: 1,
            renderer: me.customerGroupColumnRenderer
        });
    },

    /**
     * Creates the end price column for the listing
     * @return Ext.grid.column.Column
     */
    createEndPriceColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=prices/end_price}End price{/s}',
            dataIndex: 'endPrice',
            flex: 1,
            renderer: me.endPriceColumnRenderer,
            editor: {
                xtype: 'numberfield',
                allowBlank: false,
                minValue: 0.01,
                decimalPrecision: 2
            }
        });
    },

    /**
     * Creates the end price column for the listing
     * @return Ext.grid.column.Column
     */
    createPerMinuteColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=prices/per_minute}Per minute{/s}',
            dataIndex: 'perMinute',
            flex: 1,
            renderer: me.perMinuteColumnRenderer
        });
    },


    /**
     * Creates the price column for the listing
     * @return Ext.grid.column.Column
     */
    createPriceColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=prices/summarized_product_price_column}Product price{/s}',
            dataIndex: 'price',
            flex: 1,
            renderer: me.priceColumnRenderer,
            editor: {
                xtype: 'numberfield',
                allowBlank: false,
                minValue: 0.01,
                decimalPrecision: 2
            }
        });
    },

    /**
     * Creates the action column for the listing.
     * @return Ext.grid.column.Action
     */
    createActionColumn: function() {
        var me = this, items;

        items = me.getActionColumnItems();

        return Ext.create('Ext.grid.column.Action', {
            items: items,
            width: items.length * 30
        });
    },


    /**
     * Creates the action column items for the listing.
     * @return Array
     */
    getActionColumnItems: function() {
        var me = this,
                items = [];

        items.push(me.createDeleteActionColumnItem());
        return items;
    },

    /**
     * Creates the delete action column item for the listing action column
     * @return Object
     */
    createDeleteActionColumnItem: function() {
        var me = this;

        return {
            iconCls:'sprite-minus-circle-frame',
            width: 30,
            tooltip: '{s name=prices/delete_price_column}Delete price{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('deletePrice', [ record ]);
            }
        };
    },


    /**
     * Creates the tool bar for the listing component.
     * @return Ext.toolbar.Toolbar
     */
    createToolBar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolBarItems(),
            dock: 'top'
        });
    },

    /**
     * Creates the elements for the listing toolbar.
     * @return Array
     */
    createToolBarItems: function() {
        var me = this;

        return [
            { xtype: 'tbspacer', width: 6 },
            me.createToolBarCustomerGroupComboBox()
        ];
    },

    /**
     * Creates the customer group combo box for the live shopping customer group listing.
     * @return Ext.form.field.ComboBox
     */
    createToolBarCustomerGroupComboBox: function() {
        var me = this;

        me.customerGroupComboBox = Ext.create('Ext.form.field.ComboBox', {
            store: me.customerGroupStore,
            queryMode: 'local',
            name: 'customerGroup',
            margin: '0 0 9 0',
            displayField: 'name',
            valueField: 'id',
            fieldLabel: '{s name=prices/add_price_field}Add price{/s}',
            labelWidth: 180,
            width: 400,
            listeners: {
                select: function(combo, record) {
                    me.fireEvent('addPrice', record[0]);
                }
            }
        });
        return me.customerGroupComboBox;
    },

    /**
     * Creates the cell editor plugin for the listing component.
     * @return Ext.grid.plugin.CellEditing
     */
    createCellEditor: function() {
        var me = this;

        me.cellEditor = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1,
            listeners: {
                edit: function(editor, event) {
                    me.fireEvent('changePrice', event.record, editor, event);
                }
            }
        });
        return me.cellEditor;
    },


    /**
     * Renderer function of the customer group column.
     * @param value
     * @param metaData
     * @param record Ext.data.Model
     */
    customerGroupColumnRenderer: function(value, metaData, record) {
        var me = this;

        if (!(record instanceof Ext.data.Model)) {
            return '';
        }

        if (record.getCustomerGroupStore instanceof Ext.data.Store && record.getCustomerGroupStore.first() instanceof Ext.data.Model) {
            return record.getCustomerGroupStore.first().get('name');
        } else {
            return '';
        }
    },

    /**
     * Renderer function for the total price column.
     * @param value
     * @param metaData
     * @param record
     */
    priceColumnRenderer: function(value, metaData, record) {
        var me = this;

        return Ext.util.Format.number(value);
    },

    /**
     * Renderer function for the end price column.
     * @param value
     * @param metaData
     * @param record
     */
    endPriceColumnRenderer: function(value, metaData, record) {
        return Ext.util.Format.number(value);
    },

    /**
     * Renderer function for the per minute column.
     * @param value
     * @param metaData
     * @param record
     */
    perMinuteColumnRenderer: function(value, metaData, record) {
        var me = this;

        if (record.getCustomerGroupStore instanceof Ext.data.Store && record.getCustomerGroupStore.getCount() > 0) {
            var price = me.liveShoppingController.getPerMinute(record);
            return Ext.util.Format.number(price);
        }

        return 0;
    }

});
//{/block}