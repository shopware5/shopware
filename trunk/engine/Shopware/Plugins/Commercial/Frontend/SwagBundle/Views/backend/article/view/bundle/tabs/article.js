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
Ext.define('Shopware.apps.Article.view.bundle.tabs.Article', {

    extend: 'Ext.grid.Panel',

    title: '{s name=articles/title}Products{/s}',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.bundle-article-listing',

    /**
     * Reference to the bundle controller to use the global function to calculate
     * the total amount for each customer group.
     * Shopware.apps.Article.controller.Bundle
     */
    bundleController: null,

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
        me.summaryFeature = me.createSummaryFeature();
        me.features = [ me.summaryFeature ];
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
             * Fired when the user select an article in the suggest search.
             * @param Ext.data.Model The selected record
             */
            'addBundleArticle',

            /**
             * @Event
             * Custom component event.
             * Fired when the user clicks the delete action column item.
             * @param Ext.data.Model The row record
             */
            'deleteBundleArticle',

            /**
             * @Event
             * Custom component event.
             * Fired after the user change a bundle article over the row editor.
             * @param Ext.data.Model The row record
             */
            'changeBundleArticle',

            /**
             * @Event
             * Custom component event.
             * Fired when the customer clicks the "open article" action column item.
             * @param int article id
             */
            'openArticle'
        )
    },

    /**
     * Creates the columns for the grid panel.
     * @return Array
     */
    createColumns: function() {
        var me = this, columns = [];

        columns.push(me.createNumberColumn());
        columns.push(me.createNameColumn());
        columns.push(me.createQuantityColumn());
        columns.push(me.createIsConfiguratoreColumn());
        columns.push(me.createConfigurableColumn());
        me.customerGroupStore.each(function(customerGroup) {
            if (customerGroup) {
                columns.push(me.createCustomerGroupPriceColumn(customerGroup));
            }
        });

        columns.push(me.createActionColumn());

        return columns;
    },

    /**
     * Creates the number column for the listing
     * @return Ext.grid.column.Column
     */
    createNumberColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/article_number_column}Product number{/s}',
            dataIndex: 'articleDetail.number',
            flex: 1,
            renderer: me.articleNumberColumnRenderer
        });
    },

    /**
     * Creates the name column for the listing.
     * @return Ext.grid.column.Column
     */
    createNameColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/article_name_column}Product name{/s}',
            dataIndex: 'articleDetail.name',
            flex: 1,
            renderer: me.articleNameColumnRenderer
        });
    },

    /**
     * Creates the quantity column for the listing.
     * @return Ext.grid.column.Column
     */
    createQuantityColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/quantity_column}Quantity{/s}',
            dataIndex: 'quantity',
            flex: 1,
            editor: {
                xtype: 'numberfield',
                allowBlank: false,
                minValue: 1,
                decimalPrecision: 0
            }
        });
    },

    /**
     * Creates the configurable flag column for the listing.
     * @return Ext.grid.column.Column
     */
    createIsConfiguratoreColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/is_configurator_column}Is configurator{/s}',
            dataIndex: 'isConfigurator',
            flex: 1,
            sortable: false,
            renderer: me.isConfiguratorColumnRenderer
        });
    },


    /**
     * Creates the configurable flag column for the listing.
     * @return Ext.grid.column.Column
     */
    createConfigurableColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/configurable}Configurable{/s}',
            dataIndex: 'configurable',
            flex: 1,
            editor: {
                xtype: 'checkbox',
                inputValue: true,
                uncheckedValue: false
            }
        });
    },

    /**
     * Creates a dynamic column for the passed customer group to display
     * the customer group prices in the listing.
     * @param customerGroup
     * @return Ext.grid.column.Column
     */
    createCustomerGroupPriceColumn: function(customerGroup) {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: customerGroup.get('name') + ' ' + '{s name=articles/price_column}price{/s}',
            customerGroup: customerGroup,
            dataIndex: 'customerGroup.id',
            flex: 1,
            renderer: me.customerGroupPriceRenderer,
            summaryType: 'sum',
            //renderer for the summary row
            summaryRenderer: function(value, summaryData, dataIndex) {
                var column = me.bundleController.getColumnByDataIndex(me.columns, dataIndex);
                var price = me.bundleController.getTotalAmountForCustomerGroup(
                    column.customerGroup,
                    me.customerGroupStore
                );
                return '<b>' + Ext.util.Format.number(price) + '</b>';
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
        items.push(me.createOpenArticleActionColumnItem());
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
            tooltip: '{s name=articles/delete_article_column}Delete product{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('deleteBundleArticle', [ record ]);
            }
        };
    },


    /**
     * Creates the open article action column item for the listing action column
     * @return Object
     */
    createOpenArticleActionColumnItem: function() {
        var me = this;

        return {
            iconCls: 'sprite-inbox--arrow',
            cls: 'open-article',
            tooltip: '{s name=articles/open_article_column}Open product{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                var articleId = record.getArticleDetail().first().get('articleId');
                me.fireEvent('openArticle', articleId);
            }
        };
    },

    /**
     * Creates the summary feature for the listing component.
     * @return Ext.grid.feature.Summary
     */
    createSummaryFeature: function() {
        var me = this;
        return Ext.create('Ext.grid.feature.Summary');
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
                    me.fireEvent('changeBundleArticle', event.record);
                }
            }
        });
        return me.cellEditor;
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
        items.push(me.createToolBarArticleSearch());
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
     * Creates the article suggest search for the toolbar to add
     * a new article to the bundle.
     * @return Shopware.form.field.ArticleSearch
     */
    createToolBarArticleSearch: function() {
        var me = this;

        //create an own search store because we need the whole article prices.
        me.searchStore = Ext.create('Shopware.apps.Article.store.bundle.Search');

        //create the article search component
        me.articleSearch = Ext.create('Shopware.form.field.ArticleSearch', {
            name: 'number',
            fieldLabel: '{s name=articles/add_article_field}Add product{/s}',
            returnValue: 'name',
            hiddenReturnValue: 'number',
            width: 400,
            formFieldConfig: {
                labelWidth: 180,
                width: 400
            },
            articleStore: me.searchStore,
            listeners: {
                //we have to enable the add buton after the user select an article record.
                valueselect: function(field, name, number, record) {
                    if (record instanceof Ext.data.Model) {
                        me.fireEvent('addBundleArticle', record);
                    }
                }
            }
        });
        //fix: to change the drop down store. Can't be defined in the component construct, because it will be overriden.
        me.articleSearch.dropDownStore = me.searchStore;

        //fix: we have to change the store of the drop down menu.
        me.articleSearch.getDropDownMenu().bindStore(me.searchStore);

        //fix: we have to redeclare the data change evnt on the search store.
        me.searchStore.on('datachanged', me.articleSearch.onSearchFinish, me.articleSearch);

        return me.articleSearch;
    },

    /**
     * Renderer function for the article number column.
     * @return String
     */
    articleNumberColumnRenderer: function(value, metaData, record) {
        var me = this;
        if (record.getArticleDetail() instanceof Ext.data.Store && record.getArticleDetail().first() instanceof Ext.data.Model) {
            return record.getArticleDetail().first().get('number');
        } else {
            return '';
        }
    },

    /**
     * Renderer function for the article name column.
     * @param value
     * @param metaData
     * @param record
     * @return String
     */
    articleNameColumnRenderer: function(value, metaData, record) {
        var me = this;

        if (record.getArticleDetail() instanceof Ext.data.Store && record.getArticleDetail().first() instanceof Ext.data.Model) {
            return record.getArticleDetail().first().raw.article.name;
        } else {
            return '';
        }
    },

    /**
     * Renderer function of the isConfigurator column.
     * @param value
     * @param metaData
     * @param record
     */
    isConfiguratorColumnRenderer: function(value, metaData, record) {
        var me = this;

        if (record.getArticleDetail() instanceof Ext.data.Store && record.getArticleDetail().first() instanceof Ext.data.Model) {
            if (record.getArticleDetail().first().raw.article.configuratorSetId > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    },

    /**
     * Renderer function for the customer group price for the selected article variant and the customer group of this
     * columns and the selected quantity.
     * @return String
     */
    customerGroupPriceRenderer: function(value, metaData, record, rowIndex, colIndex) {
        var me = this;
        var column = me.columns[colIndex];

        if (record.getArticleDetail() instanceof Ext.data.Store && record.getArticleDetail().first() instanceof Ext.data.Model) {
            var detail = record.getArticleDetail().first();
            var prices = detail.getPrice();
            var quantity = record.get('quantity');

            if (!quantity > 0) {
                quantity = 1;
            }
            if (prices instanceof Ext.data.Store && prices.getCount() > 0) {
                var customerGroupPrice = me.bundleController.getPriceForCustomerGroupAndQuantity(prices, column.customerGroup, quantity);
                if (customerGroupPrice === null) {
                    customerGroupPrice = me.bundleController.getPriceForCustomerGroupAndQuantity(prices, me.customerGroupStore.first(), quantity);
                }
                var price = customerGroupPrice.get('price') * quantity;

                return Ext.util.Format.number(price);
            } else {
                return '';
            }

        } else {
            return '';
        }
    }

});