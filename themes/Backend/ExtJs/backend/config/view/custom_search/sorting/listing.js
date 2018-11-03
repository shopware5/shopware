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

//{namespace name=backend/custom_search/translation}

//{block name="backend/config/view/custom_search/sorting/listing"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.Listing', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.config-custom-sorting-listing',

    configure: function() {
        return {
            deleteButton: false,
            editColumn: false,
            pagingbar: false,
            displayProgressOnSingleDelete: false,
            columns: {
                label: {
                    header: '{s name="sorting_label"}{/s}',
                    sortable: false
                },
                active: {
                    header: '{s name="active"}{/s}',
                    sortable: false
                },
                displayInCategories: {
                    header: '{s name="display_in_categories"}{/s}',
                    sortable: false
                }
            }
        };
    },

    createColumns: function() {
        var me = this,
            columns = me.callParent(arguments);

        columns = Ext.Array.insert(columns, 0, [me.createSortingColumn()]);

        me.viewConfig = {
            plugins: {
                ptype: 'gridviewdragdrop',
                ddGroup: 'custom-sorting-drag-and-drop',
            },
            listeners: {
                'drop': Ext.bind(me.onDrop, me)
            }
        };

        return columns;
    },

    createSortingColumn: function() {
        var me = this;

        return {
            width: 24,
            hideable: false,
            renderer : me.renderSorthandleColumn
        };
    },

    renderSorthandleColumn: function (value, metadata) {
        return '<div style="cursor: n-resize;">&#009868;</div>';
    },

    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.RowModel', {
            listeners: {
                selectionchange: function (selModel, selection) {
                    return me.onSelectionChange(selModel, selection);
                }
            }
        });
    },

    onDrop: function(node, data, overModel, dropPosition, eOpts ) {
        var me = this,
            position = 0,
            model = data.records.shift();

        if (dropPosition == 'before') {
            position = overModel.get('position') - 1;
        } else {
            position = overModel.get('position') + 1;
        }

        Ext.Ajax.request({
            url: '{url controller=customSorting action=changePosition}',
            method: 'POST',
            params: {
                id: model.get('id'),
                position: position
            },
            success: function(operation, opts) {
                me.getStore().load();
            }
        });
    },

    onAddItem: function() {
        var me = this;
        me.sortingForm.setDisabled(false);
        me.sortingForm.loadRecord(
            Ext.create('Shopware.apps.Base.model.CustomSorting', {
                displayInCategories: true,
                active: true
            })
        );
        me.sortingForm.down('field[name=label]').focus();
    },

    onSelectionChange: function(selModel, selection) {
        var me = this;

        if (selection.length <= 0) {
            me.sortingForm.setDisabled(true);
            return;
        }
        me.onLoadSorting(selection[0]);
    },

    onLoadSorting: function(record) {
        var me = this;
        me.sortingForm.setDisabled(false);
        me.sortingForm.loadRecord(record);
    }
});

//{/block}
