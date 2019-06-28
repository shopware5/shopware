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
 * @package    Category
 * @subpackage Controller
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/category/sorting}
//{block name="backend/category/controller/manual_sort"}
Ext.define('Shopware.apps.Category.controller.ManualSorting', {
    extend: 'Enlight.app.Controller',

    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        {
            ref: 'table',
            selector: 'category-custom-sort-table'
        },
        {
            ref: 'grid',
            selector: 'manual-sort-products-grid'
        },
    ],

    init: function () {
        var me = this;

        me.control({
            'category-custom-sort-table dataview': {
                drop: me.onDropTable,
            },
            'category-custom-sort-table': {
                unpin: me.unPin,
                singleItemPositionUpdated: me.singleItemPositionUpdated,
                moveToPrevPage: me.tableMoveToPrevPage,
                moveToNextPage: me.tableMoveToNextPage,
            },
            'manual-sort-products-grid': {
                drop: me.onDropGrid,
                unpin: me.unPin,
                moveToPrevPage: me.gridMoveToPrevPage,
                moveToNextPage: me.gridMoveToNextPage,
            },
            'manual-sort-tab productsearchfield': {
                valueselect: me.onProductSelected
            },
            'manual-sort-tab': {
                'layout-button-click': me.toggleLayout,
                'reset-category': me.resetCategory,
            }
        });

        me.callParent(arguments);
    },

    onDropTable: function (dragZone, element) {
        this.drop(this.getTable().store, element.records);
    },

    onDropGrid: function(store, records, targetRecord) {
        Ext.each(records, function (record) {
            store.remove(record);
            store.insert(store.indexOf(targetRecord), record);
        });

        this.drop(store, records);
    },

    drop: function (store, droppedItems) {
        var pageIndex = (store.currentPage - 1)* store.pageSize,
            data = {};

        Ext.each(droppedItems, function (droppedItem) {
            data[droppedItem.get('id')] = pageIndex + (store.indexOf(droppedItem) + 1)
        });


        Ext.Ajax.request({
            url: '{url controller=ManualSorting action=assignPosition}?' + Ext.urlEncode(this.getTable().getStore().getProxy().extraParams),
            jsonData: JSON.stringify({
                data: data
            }),
            method: 'POST',
            success: function () {
                store.load();
            }
        });
    },

    singleItemPositionUpdated: function(record) {
        var store = this.getTable().getStore(),
            data = {};

        data[record.get('id')] = record.get('position');

        Ext.Ajax.request({
            url: '{url controller=ManualSorting action=assignPosition}?' + Ext.urlEncode(this.getTable().getStore().getProxy().extraParams),
            jsonData: JSON.stringify({
                data: data
            }),
            method: 'POST',
            success: function () {
                store.load();
            }
        });
    },

    gridMoveToNextPage: function() {
        var selectedRecord = this.getGrid().dataView.getSelectionModel().getSelection()[0],
            store = this.getGrid().store,
            newPage = store.currentPage + 1,
            newPosition = ((newPage -1) * store.pageSize) + 1;

        selectedRecord.set('position', newPosition);

        this.singleItemPositionUpdated(selectedRecord);
    },

    gridMoveToPrevPage: function() {
        var selectedRecord = this.getGrid().dataView.getSelectionModel().getSelection()[0],
            store = this.getGrid().store,
            newPage = store.currentPage - 1,
            newPosition = ((newPage -1) * store.pageSize) + 1;

        selectedRecord.set('position', newPosition);

        this.singleItemPositionUpdated(selectedRecord);
    },

    tableMoveToPrevPage: function(record) {
        var store = this.getTable().store,
            newPage = store.currentPage - 1,
            newPosition = ((newPage -1) * store.pageSize) + 1;

        record.set('position', newPosition);

        this.singleItemPositionUpdated(record);
    },

    tableMoveToNextPage: function(record) {
        var store = this.getTable().store,
            newPage = store.currentPage + 1,
            newPosition = ((newPage -1) * store.pageSize) + 1;

        record.set('position', newPosition);

        this.singleItemPositionUpdated(record);
    },

    onProductSelected: function (field, name, productNumber, record) {
        var store = this.getTable().getStore(),
            extraParams = store.getProxy().extraParams;

        field.reset();

        Ext.Ajax.request({
            url: '{url controller=Category action=addCategoryArticles}',
            params: {
                ids: Ext.JSON.encode([record.get('id')]),
                categoryId: ~~(1 * extraParams.categoryId)
            },
            success: function () {
                var data = {};
                data[record.get('id')] = ((store.currentPage -1) * store.pageSize) + 1;

                Ext.Ajax.request({
                    url: '{url controller=ManualSorting action=assignPosition}?' + Ext.urlEncode(extraParams),
                    jsonData: JSON.stringify({
                        data: data
                    }),
                    method: 'POST',
                    success: function () {
                        store.load();
                    }
                });
            }
        });
    },

    unPin: function (record) {
        var me = this;
        record.set('position', null);
        Ext.Ajax.request({
            url: '{url controller=ManualSorting action=removePosition}?' + Ext.urlEncode(this.getTable().getStore().getProxy().extraParams),
            jsonData: JSON.stringify({
                data: record.data
            }),
            method: 'POST',
            success: function () {
                me.getTable().store.load();
            }
        });
    },

    toggleLayout: function (tab, activeItem) {
        if (activeItem.layout === 'grid') {
            tab.grid.hide();
            tab.table.show();
        } else {
            tab.table.hide();
            tab.grid.show();
        }
    },

    resetCategory: function (categoryId) {
        var store = this.getTable().getStore();

        Ext.MessageBox.confirm('{s name="reset_category_btn"}{/s}', '{s name="reset_category_btn_confirm"}{/s}', function (answer) {
            if (answer !== 'yes') {
                return;
            }

            Ext.Ajax.request({
                url: '{url controller=ManualSorting action=resetCategory}?categoryId=' + categoryId,
                method: 'POST',
                success: function () {
                    store.load();
                }
            });
        })
    }
});
//{/block}
