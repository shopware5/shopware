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
    allowBlank: true,

    fieldLabelConfig: 'default',

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

    useSeparator: true,

    /**
     * @boolean
     */
    ignoreDisabled: true,

    allowDelete: true,

    allowAdd: true,

    initComponent: function() {
        var me = this;

        if ((!Ext.isDefined(this.store) || this.store === null) && Ext.isDefined(this.model)) {
            var factory = Ext.create('Shopware.attribute.SelectionFactory');
            this.store = factory.createEntitySearchStore(this.model);
            this.searchStore = factory.createEntitySearchStore(this.model);
        }

        me.store = me.initializeStore();
        me.items = me.createItems();

        if (me.fieldLabelConfig !== 'default') {
            me.fieldLabel = '';
        }

        me.callParent(arguments);
    },

    initializeStore: function() {
        var me = this;

        return Ext.create('Ext.data.Store', {
            model: me.store.model,
            proxy: me.store.getProxy(),
            remoteSort: me.store.remoteSort,
            remoteFilter: me.store.remoteFilter,
            sorters: me.store.getSorters(),
            filters: me.store.filters.items
        });
    },

    createItems: function() {
        var me = this, items = [];

        me.grid = me.createGrid();
        me.searchField = me.createSearchField();

        items.push(me.searchField);
        items.push(me.grid);

        if (me.supportText) {
            items.push(me.createSupportText(me.supportText));
        }
        return items;
    },

    createGrid: function() {
        var me = this;
        var viewConfig = { };

        if (me.allowSorting) {
            viewConfig = {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    ddGroup: 'shopware-form-field-grid' + this.id
                },
                listeners: {
                    drop: function () {
                        me.fireEvent('change', me, me.getValue());
                    }
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

        if (!me.allowSorting) {
            return {
                hidden: true
            };
        }

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
        var items = [];

        if (this.allowDelete) {
            items.push(this.createDeleteColumn());
        }

        return items;
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
        this.fireEvent('change', this, this.getValue());
        me.fixLayout();
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
        me.fixLayout();

        this.fireEvent('change', this, this.getValue());

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

        var emptyText = '';
        if (me.fieldLabelConfig === 'as_empty_text') {
            emptyText = me.fieldLabel;
        }

        return {
            emptyText: emptyText,
            helpText: me.helpText,
            helpTitle: me.helpTitle,
            store: me.searchStore,
            multiSelect: true,
            margin: margin,
            hidden: !me.allowAdd,
            isFormField: false,
            pageSize: me.searchStore.pageSize,
            listeners: {
                beforeselect: function (combo, records) {
                    return me.onBeforeSelect(combo, records);
                },
                select: function(combo, records) {
                    return me.onSelect(combo, records);
                }
            }
        };
    },

    /**
     * allows to override the select event
     * @param combo
     * @param records
     */
    onSelect: function(combo, records) {

    },

    onBeforeSelect: function(combo, records) {
        var me = this, added = false;

        Ext.each(records, function(record) {
            added = me.addItem(record);
            me.animateAdded(combo, added, record);
        });
        return false;
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
        var me = this, recordData = [], store = me.store;

        if (me.isDisabled() && !me.ignoreDisabled) {
            return null;
        }

        store.each(function(item) {
            recordData.push(me.getItemData(item));
        });

        if (recordData.length <= 0) {
            return null;
        }

        if (!me.useSeparator) {
            return recordData;
        }
        return me.separator + recordData.join(me.separator) + me.separator;
    },

    setValue: function(value) {
        var me = this;

        me.store.removeAll();
        if (!value) {
            me.isValid();
            me.fixLayout();
            return;
        }

        try {
            var ids = value;
            if (me.useSeparator) {
                ids = value.split(me.separator);
            }
            ids = ids.filter(function(id) {
                return id.length > 0 || id > 0;
            });
        } catch (e) {
            return;
        }

        if (!ids || ids.length <= 0) {
            me.isValid();
            return;
        }

        me.store.load({
            params: { ids: Ext.JSON.encode(ids) },
            callback: function() {
                me.isValid();
                me.fixLayout();
            }
        });
    },

    fixLayout: function() {
        if (!this.rendered) {
            return;
        }
        if (this.getHeight() <= 0) {
            return;
        }

        this.setHeight(this.getHeight());
    },

    getSubmitData: function() {
        var value = { };
        value[this.name] = this.getValue();
        return value;
    },

    isValid: function() {
        var me = this;

        if (me.searchField && me.searchField.combo) {
            me.searchField.combo.clearInvalid();
        }

        if (me.allowBlank) {
            return true;
        }

        if (me.store.getCount() > 0) {
            return true;
        }

        if (me.searchField && me.searchField.combo) {
            me.searchField.combo.markInvalid([
                '{s name="not_empty"}{/s}'
            ]);
        }

        return false;
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
    },

    createSupportText: function(supportText) {
        return Ext.create('Ext.Component', {
            html: '<div>'+supportText+'</div>',
            cls: Ext.baseCSSPrefix +'form-support-text'
        });
    },

    enable: function() {
        var me = this;

        me.callParent(arguments);
        if (me.grid) {
            me.grid.enable();
        }
        if (me.searchField) {
            me.searchField.enable();
        }
    },

    disable: function() {
        var me = this;

        me.callParent(arguments);

        if (me.grid) {
            me.grid.disable();
        }
        if (me.searchField) {
            me.searchField.disable();
        }
    }
});
