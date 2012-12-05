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
 * Shopware UI - Bundle article list
 * The article list of the bundle module grants the user to see all
 * defined article positions of the bundle, compacted in a small overview.
 */
//{namespace name="backend/bundle/bundle/view/main"}
//{block name="backend/bundle/view/list/articles"}
Ext.define('Shopware.apps.Bundle.view.list.Articles', {

    extend: 'Ext.grid.Panel',

    border: false,

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'bundle-article-list',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.bundle-article-list',
    /**
     * Called when the component will be initialed.
     */
    initComponent: function() {
        var me = this;
        me.registerEvents();
        me.columns = me.createColumns();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents('openArticle');
    },


    /**
     * Creates the grid columns.
     */
    createColumns: function() {
        var me = this;

        return [
            me.createNumberColumn(),
            me.createNameColumn(),
            me.createConfigurableColumn(),
            me.createQuantityColumn(),
            me.createActionColumn()
        ];
    },

    /**
     * Creates the number column for the article grid.
     * @return Object
     */
    createNumberColumn: function() {
        var me = this;

        return {
            header: '{s name=articles/article_number_column}Product number{/s}',
            flex: 2,
            dataIndex: 'number',
            renderer: me.numberColumnRenderer
        };
    },

    /**
     * Creates the name column for the article grid
     * @return Object
     */
    createNameColumn: function() {
        var me = this;

        return {
            header: '{s name=articles/article_name_column}Product name{/s}',
            flex: 2,
            dataIndex: 'name',
            renderer: me.nameColumnRenderer
        }
    },

    /**
     * Creates the configurator flag column.
     * @return Object
     */
    createConfigurableColumn: function() {
        var me = this;

        return {
            header: '{s name=articles/configurable_column}Configurable{/s}',
            flex: 1,
            dataIndex: 'configurable',
            renderer: me.configurableColumnRenderer
        }
    },

    /**
     * Creates the quantity column for the article grid.
     * @return Object
     */
    createQuantityColumn: function() {
        var me = this;

        return {
            header: '{s name=articles/quantity_column}Quantity{/s}',
            flex: 1,
            dataIndex: 'quantity',
            renderer: me.quantityColumnRenderer
        }
    },

    /**
     * Creates the action columns for the article grid.
     * @return Ext.grid.column.Action
     */
    createActionColumn: function() {
        var me = this;

        var items = me.getActionColumnItems();

        return Ext.create('Ext.grid.column.Action', {
            items: items,
            width: items.length * 30
        });
    },

    /**
     * Creates the different action column items for the action
     * column of the article grid.
     * @return Array
     */
    getActionColumnItems: function() {
        var me = this,
            items = [];

        items.push(me.createOpenArticleItem());

        return items;
    },

    /**
     * Creates open article action column item for the article grid.
     * The action column items opens the article detail page of the
     * selected article.
     * @return Object
     */
    createOpenArticleItem: function() {
        var me = this;

        return {
            iconCls: 'sprite-inbox--arrow',
            cls: 'open-article',
            tooltip: '{s name=articles/open_product_column}Open product{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                var articleId = record.getDetail().first().get('articleId');
                me.fireEvent('openArticle', articleId);
            }
        };
    },

    /**
     * Column renderer function for the article number column
     * @param value
     * @param metaData
     * @param record
     */
    numberColumnRenderer: function(value, metaData, record) {
        var number = value;
        if (record && record.getDetail() instanceof Ext.data.Store && record.getDetail().first() instanceof Ext.data.Model) {
            number = record.getDetail().first().get('number');
        }
        return number;
    },

    /**
     * Column renderer function for the article column
     * @param value
     * @param metaData
     * @param record
     */
    nameColumnRenderer: function(value, metaData, record) {
        var name = value;
        if (record && record.getDetail() instanceof Ext.data.Store && record.getDetail().first() instanceof Ext.data.Model) {
            var detail = record.getDetail().first();
            name = detail.raw.article.name;
        }
        return name;
    },

    /**
     * Column renderer function for the configurable column
     * @param value
     * @param metaData
     * @param record
     */
    configurableColumnRenderer: function(value, metaData, record) {
        return record.get('configurable');
    },

    /**
     * Column renderer function for the quantity column
     * @param value
     * @param metaData
     * @param record
     */
    quantityColumnRenderer: function(value, metaData, record) {
        return record.get('quantity');
    }

});

//{/block}
