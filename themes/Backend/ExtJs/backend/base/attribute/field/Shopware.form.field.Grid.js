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
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name="backend/attributes/fields"}

Ext.define('Shopware.form.field.Grid', {
    extend: 'Ext.form.FieldContainer',
    alias: 'widget.shopware-form-field-grid',
    cls: 'shopware-form-field-grid',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    mixins: {
        formField: 'Ext.form.field.Base',
        factory: 'Shopware.attribute.SelectionFactory'
    },
    height: 230,
    maxHeight: 230,
    hideHeaders: true,
    baseBodyCls: Ext.baseCSSPrefix + 'form-item-body shopware-multi-selection-form-item-body',
    separator: '|',

    /**
     * @required
     */
    store: null,

    /**
     * @required
     */
    searchStore: null,

    /**
     * @boolean
     */
    allowSorting: true,

    /**
     * @boolean
     */
    animateAddItem: true,

    initComponent: function() {
        var me = this;

        var store = me.store;
        me.store = Ext.create('Ext.data.Store', {
            model: store.model,
            proxy: store.getProxy()
        });

        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;
        me.grid = me.createGrid();
        me.searchField = me.createSearchField();
        return [me.searchField, me.grid];
    },

    createGrid: function() {
        var me = this;
        var viewConfig = { };

        if (me.allowSorting) {
            viewConfig = {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    ddGroup: 'shopware-form-field-grid' + this.id
                }
            };
        }

        return Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            store: me.store,
            border: false,
            flex: 1,
            viewConfig: viewConfig,
            hideHeaders: me.hideHeaders
        });
    },

    createSearchField: function() {
        return Ext.create('Shopware.form.field.SingleSelection', this.getComboConfig());
    },

    createColumns: function() {
        var me = this, columns = [];

        if (me.allowSorting) {
            columns.push(me.createSortingColumn());
        }
        columns.push({
            dataIndex: 'id',
            hidden: true
        });
        columns.push({
            dataIndex: 'label',
            flex: 1,
            renderer: me.labelRenderer,
            scope: me
        });
        columns.push(me.createActionColumn());
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

    createActionColumn: function() {
        var items = this.createActionColumnItems();
        return {
            xtype: 'actioncolumn',
            width: 30 * items.length,
            items: items
        };
    },

    createActionColumnItems: function() {
        return [this.createDeleteColumn()];
    },

    createDeleteColumn: function() {
        var me = this;
        return {
            action: 'delete',
            iconCls: 'sprite-minus-circle-frame',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.removeItem(record);
            }
        };
    },

    insertGlobeIcon: function(icon) {
        var me = this;
        icon.set({
            cls: Ext.baseCSSPrefix + 'translation-globe sprite-globe',
            style: 'position: absolute;width: 16px; height: 16px;display:block;cursor:pointer;top:6px;right:6px;'
        });
        if (me.searchField.el) {
            icon.insertAfter(me.searchField.el);
        }
    },

    removeItem: function(record) {
        var me = this;
        me.store.remove(record);
    },

    /**
     * @param record
     * @returns { boolean } true if added
     */
    addItem: function(record) {
        var exist = false;
        var me = this;
        var newData = me.getItemData(record);

        this.store.each(function(item) {
            var data = me.getItemData(item);
            if (data == newData) {
                exist = true;
                return false;
            }
        });

        if (!exist) {
            this.store.add(record);
        }
        return !exist;
    },

    getItemData: function(item) {
        return item.get('id');
    },

    getComboConfig: function() {
        var me = this;
        var margin = 0;
        if (me.translatable == true) {
            margin = '0 25 0 0';
        }

        return {
            store: me.searchStore,
            multiSelect: true,
            margin: margin,
            isFormField: false,
            pageSize: me.searchStore.pageSize,
            listeners: {
                beforeselect: function (combo, records) {
                    var added = false;
                    Ext.each(records, function(record) {
                        added = me.addItem(record);
                        me.animateAdded(combo, added, record);
                    });
                    return false;
                }
            }
        };
    },

    animateAdded: function(combo, added, record) {
        if (!this.animateAddItem) {
            return;
        }

        try {
            var el = combo.picker.getNode(record);
            if (added) {
                el.style.background = 'rgba(0, 212, 0, 0.3)';
            } else {
                el.style.background = 'rgba(255, 0, 0, 0.3)'
            }

            Ext.Function.defer(function() {
                el.style.background = 'none';
            }, 500);
        } catch (e) {
        }
    },

    getValue: function() {
        var me = this;
        var recordData = [];
        var store = me.store;

        store.each(function(item) {
            recordData.push(me.getItemData(item));
        });

        if (recordData.length <= 0) {
            return null;
        }

        return me.separator + recordData.join(me.separator) + me.separator;
    },

    setValue: function(value) {
        var me = this;

        me.store.removeAll();
        if (!value) {
            return;
        }

        try {
            var ids = value.split(me.separator);
            ids = ids.filter(function(value) {
                return value.length > 0;
            });
        } catch (e) {
            return;
        }

        if (!ids || ids.length <= 0) {
            return;
        }

        me.store.load({
            params: { ids: Ext.JSON.encode(ids) }
        });
    },

    getSubmitData: function() {
        var value = { };
        value[this.name] = this.getValue();
        return value;
    },

    labelRenderer: function(value, meta, record) {
        var field = this.getLabelField(record);
        if (!field) {
            return value;
        }
        return record.get(field);
    },

    renderSorthandleColumn: function (value, metadata) {
        return '<div style="cursor: n-resize;">&#009868;</div>';
    }
});