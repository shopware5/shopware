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
 * @package    Article
 * @subpackage Bundle
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name="backend/bundle/article/view/main"}
Ext.define('Shopware.apps.Article.view.bundle.tabs.CustomerGroup', {

    extend: 'Ext.grid.Panel',

    title: '{s name=customergroups/title}Customergroups{/s}',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.bundle-customer-group-listing',

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
             * Fired when the user opens the toolbar customer group combo box
             * and select a combo box row.
             * @param Ext.data.Model The selected record
             */
            'addCustomerGroup',

            /**
             * @Event
             * Custom component event.
             * Fired when the user clicks the delete action column item
             * in the listing.
             * @param Ext.data.Model The row record
             */
            'deleteCustomerGroup'
        );
    },

    /**
     * Creates the columns for the grid panel.
     * @return Array
     */
    createColumns: function() {
        var me = this, columns = [];
        columns.push(me.createNameColumn());
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
            header: '{s name=customergroups/name_column}Customergroup{/s}',
            dataIndex: 'name',
            flex: 1
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
            tooltip: '{s name=customergroups/delete_column}Delete customergroup{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('deleteCustomerGroup', [ record ]);
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
        var me = this, items = [];

        items.push(me.createToolBarSpacer(6));
        items.push(me.createToolBarCustomerGroupComboBox());
        return items;
    },

    /**
     * Creates a toolbar spacer with the passed width value.
     * @param width
     * @return Ext.toolbar.Spacer
     */
    createToolBarSpacer: function(width) {
        var me = this;

        return Ext.create('Ext.toolbar.Spacer', {
            width: width
        });
    },

    /**
     * Creates the customer group combo box for the bundle customer group listing.
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
            fieldLabel: '{s name=customergroups/add_customergroup_field}Add customergroup{/s}',
            labelWidth: 180,
            width: 400,
            listeners: {
                select: function(combo, record) {
                    me.fireEvent('addCustomerGroup', record[0]);
                }
            }
        });
        return me.customerGroupComboBox;
    }


});