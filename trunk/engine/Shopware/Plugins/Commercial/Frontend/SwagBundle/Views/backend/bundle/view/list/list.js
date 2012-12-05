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
 * @package    Bundle
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Bundle list
 * The bundle list component of the bundle overview
 * displays all defined bundles in the shop.
 */
//{namespace name="backend/bundle/bundle/view/main"}
//{block name="backend/bundle/view/list/list"}
Ext.define('Shopware.apps.Bundle.view.list.List', {

    extend: 'Ext.grid.Panel',
    border: false,

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'bundle-bundle-list',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.bundle-bundle-list',

    /**
     * Called when the component will be initialed.
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();
        me.columns = me.createColumns();
        me.tbar = me.getToolbar();
        me.bbar = me.getPagingBar();
        me.selModel = me.createSelectionModel();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events
     */
    registerEvents: function() {
        this.addEvents(
            'selectBundle',
            'openArticle',
            'searchBundle'
        );
    },

    getToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            items: me.createToolbarItems()
        });
    },

    createToolbarItems: function() {
        var me = this, items = [];

        items.push('->');
        items.push(me.createSearchField());

        return items;
    },

    createSearchField: function() {
        var me = this;

        return Ext.create('Ext.form.field.Text', {
            name : 'searchfield',
            action : 'search',
            width: 170,
            cls: 'searchfield',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            emptyText: '{s name=search_field_text}Search{/s}',
            listeners: {
                change: function(field, value) {
                    me.fireEvent('searchBundle', value);
                }
            }
        });
    },


    /**
     * Creates the grid selection model.
     */
    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.RowModel', {
            listeners: {
                selectionchange: function(view, selected) {
                    me.fireEvent('selectBundle', selected[0]);
                }
            }
        });
    },

    /**
     * Creates the paging toolbar for the bundle listing.
     *
     * @return Object
     */
    getPagingBar: function() {
        var me = this;

        return {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        };
    },


    /**
     * Creates the grid column
     * @return Array
     */
    createColumns: function() {
        var me = this;

        return [
            me.createNameColumn(),
            me.createArticleNameColumn(),
            me.createArticleNumberColumn(),
            me.createActiveColumn(),
            me.createTypeColumn(),
            me.createNumberColumn(),
            me.createValidFromColumn(),
            me.createValidToColumn(),
            me.createDateColumn(),
            me.createActionColumn()
        ];
    },

    /**
     * Creates the action column for the grid.
     * @return
     */
    createActionColumn: function() {
        var me = this;

        var items = me.getActionColumnItems();

        return Ext.create('Ext.grid.column.Action', {
            items: items,
            width: items.length * 30
        });
    },

    getActionColumnItems: function() {
        var me = this,
                items = [];

        items.push(me.createOpenArticleItem());

        return items;
    },

    createOpenArticleItem: function() {
        var me = this;

        return {
            iconCls: 'sprite-inbox--arrow',
            width: 30,
            tooltip: '{s name=list/open_product_column}Open product{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('openArticle', record.get('articleId'));
            }
        };
    },

    /**
     * Creates the name column for the bundle listing.
     * @return Object
     */
    createNameColumn: function() {
        var me = this;

        return {
            header: '{s name=list/name_column}Bundle name{/s}',
            flex: 1,
            dataIndex: 'bundle.name',
            renderer: me.nameColumnRenderer
        }
    },

    /**
     * Creates the article name column for the bundle listing.
     * @return Object
     */
    createArticleNameColumn: function() {
        var me = this;

        return {
            header: '{s name=list/product_name_column}Product name{/s}',
            flex: 1,
            dataIndex: 'article.name',
            renderer: me.articleNameColumnRenderer
        }
    },

    /**
     * Creates the article number column for the bundle listing.
     * @return Object
     */
    createArticleNumberColumn: function() {
        var me = this;

        return {
            header: '{s name=list/product_number_column}Product number{/s}',
            flex: 1,
            dataIndex: 'article.number',
            renderer: me.articleNumberColumnRenderer
        }
    },

    /**
     * Creates the active column for the bundle listing.
     * @return Object
     */
    createActiveColumn: function() {
        var me = this;

        return {
            header: '{s name=list/active_column}Active{/s}',
            dataIndex: 'bundle.active',
            flex: 1,
            renderer: me.activeColumnRenderer
        }
    },

    /**
     * Creates the type column for the bundle listing.
     * @return Object
     */
    createTypeColumn: function() {
        var me = this;

        return {
            header: '{s name=list/typ_column}Typ{/s}',
            dataIndex: 'bundle.type',
            flex: 1,
            renderer: me.typeColumnRenderer
        }
    },

    /**
     * Creates the order number column for the bundle listing.
     * @return Object
     */
    createNumberColumn: function() {
        var me = this;

        return {
            header: '{s name=list/bundle_number_column}Bundle number{/s}',
            flex: 1,
            dataIndex: 'bundle.number',
            renderer: me.numberColumnRenderer
        }
    },

    /**
     * Creates the valid from column for the bundle listing.
     * @return Object
     */
    createValidFromColumn: function() {
        var me = this;

        return {
            header: '{s name=list/valid_from_column}Valid from{/s}',
            flex: 1,
            dataIndex: 'bundle.validFrom',
            renderer: me.validFromColumnRenderer
        }
    },

    /**
     * Creates the valid to column for the bundle listing.
     * @return Object
     */
    createValidToColumn: function() {
        var me = this;

        return {
            header: '{s name=list/valid_to}Valid to{/s}',
            flex: 1,
            dataIndex: 'bundle.validTo',
            renderer: me.validToColumnRenderer
        }
    },

    /**
     * Creates the create date column for the bundle listing.
     * @return Object
     */
    createDateColumn: function() {
        var me = this;

        return {
            header: '{s name=list/created_at_column}Created at{/s}',
            flex: 1,
            dataIndex: 'bundle.created',
            renderer: me.dateColumnRenderer
        }
    },

    /**
     * Renderer function of the bundle name column.
     * @param value
     * @param metaData
     * @param record
     */
    nameColumnRenderer: function(value, metaData, record) {
        return record.get('name');
    },

    /**
     * Renderer function of the bundle name column.
     * @param value
     * @param metaData
     * @param record
     */
    articleNameColumnRenderer: function(value, metaData, record) {
        if (record && record.getArticle() instanceof Ext.data.Store && record.getArticle().first() instanceof Ext.data.Model) {
            return  record.getArticle().first().get('name');
        } else {
            return ''
        }
    },

    /**
     * Renderer function of the bundle name column.
     * @param value
     * @param metaData
     * @param record
     */
    articleNumberColumnRenderer: function(value, metaData, record) {
        if (record && record.getArticle() instanceof Ext.data.Store && record.getArticle().first() instanceof Ext.data.Model) {
            var article = record.getArticle().first();
            if (article instanceof Ext.data.Model && article.raw && article.raw.mainDetail) {
                return article.raw.mainDetail.number;
            } else {
                return '';
            }
        } else {
            return ''
        }
    },

    /**
     * Renderer function of the active column.
     * @param value
     * @param metaData
     * @param record
     */
    activeColumnRenderer: function(value, metaData, record) {
        return record.get('active');
    },

    /**
     * Renderer function of the bundle type column.
     * @param value
     * @param metaData
     * @param record
     */
    typeColumnRenderer: function(value, metaData, record) {
        if (record.get('type') === 1) {
            return '{s name=list/normal_bundle}Normal{/s}';
        } else if (record.get('type') === 2) {
            return '{s name=list/selectedable_bundle}Selectable{/s}';
        } else {
            return '';
        }
    },

    /**
     * Renderer function of the order number column.
     * @param value
     * @param metaData
     * @param record
     */
    numberColumnRenderer: function(value, metaData, record) {
        return record.get('number');
    },

    /**
     * Renderer function of the valid from column.
     * @param value
     * @param metaData
     * @param record
     */
    validFromColumnRenderer: function(value, metaData, record) {
        var validFrom = '';

        if (record.get('validFrom')) {
            validFrom = Ext.util.Format.date(record.get('validFrom'));
        }
        return validFrom;
    },

    /**
     * Renderer function of the valid to column.
     * @param value
     * @param metaData
     * @param record
     */
    validToColumnRenderer: function(value, metaData, record) {
        var validTo = '';

        if (record.get('validTo')) {
            validTo = Ext.util.Format.date(record.get('validTo'));
        }
        return validTo;
    },

    /**
     * Renderer function of the create date column.
     * @param value
     * @param metaData
     * @param record
     */
    dateColumnRenderer: function(value, metaData, record) {
        var createDate = new Date();

        if (record.get('created')) {
            createDate = Ext.util.Format.date(record.get('created'));
        }
        return createDate;
    }


});

//{/block}
