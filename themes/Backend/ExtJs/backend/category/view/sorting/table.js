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
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/sorting} */

//{block name="backend/category/view/sorting/table"}
Ext.define('Shopware.apps.Category.view.sorting.Table', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.category-custom-sort-table',

    initComponent: function () {
        var me = this;
        this.columns = this.getColums();
        this.viewConfig = this.getViewConfig();

        this.plugins = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            autoCancel: true,
            listeners: {
                edit: function (editor, e) {
                    me.fireEvent('singleItemPositionUpdated', e.record);
                }
            }
        });

        this.dockedItems = [this.getPagingBar()];

        this.callParent(arguments);
    },

    getColums: function () {
        return [
            {
                header: '{s name="table/column/name"}{/s}',
                dataIndex: 'name',
                flex: 1,
                sortable: false
            },
            {
                header: '{s name="table/column/active"}{/s}',
                dataIndex: 'active',
                flex: 0.2,
                sortable: false,
                renderer: this.booleanColumnRenderer
            },
            {
                header: '{s name="table/column/price"}{/s}',
                dataIndex: 'price',
                flex: 0.5,
                sortable: false
            },
            {
                header: '{s name="table/column/position"}{/s}',
                dataIndex: 'position',
                flex: 0.5,
                renderer: this.positionRenderer,
                sortable: false,
                editor: {
                    xtype: 'numberfield'
                }
            },
            {
                xtype: 'actioncolumn',
                width: 75,
                items: this.getActionColumnItems()
            }
        ]
    },

    positionRenderer: function (value) {
        if (value === null) {
            return '{s name="no_position"}{/s}';
        }

        return value;
    },

    getViewConfig: function () {
        return {
            plugins: {
                ptype: 'gridviewdragdrop',
                dragGroup: 'customSortProducts',
                dropGroup: 'customSortProducts'
            }
        };
    },

    /**
     * Shopware default renderer function for a boolean listing column.
     * This functions expects a boolean value as first parameter.
     * The function returns a span tag with a css class for a checkbox
     * sprite.
     *
     * @param { boolean|int } value
     * @return { String }
     */
    booleanColumnRenderer: function (value) {
        var checked = 'sprite-ui-check-box-uncheck';
        if (value === true || value === 1) {
            checked = 'sprite-ui-check-box';
        }
        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + checked + '"></span>';
    },

    getActionColumnItems: function () {
        var me = this,
            actionColumnData = [];

        actionColumnData.push({
            iconCls: 'sprite-arrow-turn-180-left',
            tooltip: '{s name="move_to_prev_page"}{/s}',
            getClass: function () {
                if (me.store.currentPage === 1) {
                    return 'x-hidden';
                }
            },
            handler: function (view, rowIndex) {
                me.fireEvent('moveToPrevPage', me.store.getAt(rowIndex));
            }
        });

        actionColumnData.push({
            iconCls: 'sprite-arrow-turn',
            tooltip: '{s name="move_to_next_page"}{/s}',
            getClass: function () {
                var lastPage = Math.ceil(me.store.totalCount / me.store.pageSize);

                if (lastPage <= me.store.currentPage || lastPage === 1) {
                    return 'x-hidden';
                }
            },
            handler: function (view, rowIndex) {
                me.fireEvent('moveToNextPage', me.store.getAt(rowIndex));
            }
        });

        actionColumnData.push({
            iconCls: 'sprite-cross',
            action: 'removePin',
            tooltip: '{s name="remove_position"}{/s}',
            getClass: function (value, metadata, record) {
                if (record.get('position') === null) {
                    return 'x-hidden';
                }
            },
            handler: function (view, rowIndex) {
                me.fireEvent('unpin', me.store.getAt(rowIndex));
            }
        });

        return actionColumnData;
    },

    getPagingBar: function () {
        var comboStore = Ext.create('Ext.data.Store', {
            fields: [ 'value', 'display' ],
            data: [
                { value: 25, display: '25' },
                { value: 50, display: '50' },
                { value: 100, display: '100' },
            ]
        });

        var combo = Ext.create('Ext.form.field.ComboBox', {
            store: comboStore,
            valueField: 'value',
            displayField: 'display',
            fieldLabel: '{s name="items_per_page" name=backend/customer/view/main}{/s}',
            labelStyle: 'margin-top: 2px',
            width: 220,
            labelWidth: 110,
            value: 25,
            listeners: {
                scope: this,
                change: Ext.bind(this.onPerPageChange, this)
            }
        });

        this.toolbar = Ext.create('Ext.toolbar.Paging', {
            store: this.store,
            dock: 'bottom',
            displayInfo: true
        });

        this.toolbar.add([{ xtype: 'tbspacer' }, combo]);

        return this.toolbar;
    },

    onPerPageChange: function(comp, newValue) {
        var me = this;

        me.store.pageSize = newValue;
        me.store.load();
    },

    reconfigure: function (store) {
        this.toolbar.bindStore(store);

        return this.callParent(arguments);
    }
});
//{/block}
