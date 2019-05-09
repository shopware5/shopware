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
 * @package    ProductStream
 * @subpackage Window
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_stream/main}
//{block name="backend/product_stream/view/search_grid"}
Ext.define('Shopware.apps.ProductStream.view.SearchGrid', {
    extend: 'Ext.form.FieldContainer',
    layout: { type: 'vbox', align: 'stretch' },
    border: false,
    cls: 'product-stream-search-grid',

    /**
     * @required
     */
    searchStore: null,

    /**
     * @required
     */
    store: null,

    initComponent: function() {
        this.items = this.createItems();
        this.callParent(arguments);
    },

    createItems: function() {
        return [
            this.createSearchField(),
            this.createGrid()
        ];
    },

    createSearchField: function() {
        var me = this;

        me.searchField = Ext.create('Shopware.form.field.Search', {
            store: me.searchStore,
            displayField: 'name',
            valueField: 'id',
            multiSelect: true,
            fieldLabel: '{s name=search}Search{/s}',
            pageSize: me.searchStore.pageSize,
            listeners: {
                select: function (combo, records) {
                    me.onSelectItem(combo, records);
                }
            }
        });
        return me.searchField;
    },

    createGrid: function() {
        this.grid = Ext.create('Ext.grid.Panel', {
            flex: 1,
            store: this.store,
            dockedItems: [this.createPagingBar()],
            columns: this.createColumns()
        });
        return this.grid;
    },

    createColumns: function() {
        var columns = this.createDisplayColumns();
        columns.push(this.createActionColumn());
        return columns;
    },

    createDisplayColumns: function() {
        return [{
            header: '{s name=number}Number{/s}',
            sortable: true,
            dataIndex: 'number',
            width: 110
        }, {
            header: '{s name=name}Name{/s}',
            sortable: true,
            dataIndex: 'name',
            flex: 1
        }];
    },

    createActionColumn: function() {
        return {
            xtype: 'actioncolumn',
            width: 50,
            sortable: false,
            items: this.createActionItems()
        };
    },

    createActionItems: function() {
        var me = this;
        return [{
            iconCls: 'sprite-inbox',
            tooltip: '{s name=openProduct}Open product{/s}',
            handler: function(view, rowIndex, colIndex, item, opts, record) {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Article',
                    action: 'detail',
                    params: {
                        articleId: record.get('articleId')
                    }
                });
            }
        }, {
            iconCls: 'sprite-minus-circle-frame',
            tooltip: '{s name=removeProduct}Remove product from stream{/s}',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.removeRecord(record);
            }
        }];
    },

    createPagingBar: function() {
        var me = this;
        me.pagingbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom'
        });
        return me.pagingbar;
    },

    onSelectItem: function (combo, records) {
        var me = this, inStore;

        Ext.each(records, function (record) {
            inStore = me.store.getById(record.get('id'));
            if (inStore === null) {
                me.addRecord(record);
            }
        });
        combo.setValue('');
    },

    addRecord: function(record) {
        this.store.add(record);
    },

    removeRecord: function(record) {
        this.store.remove(record);
    }
});
//{/block}
