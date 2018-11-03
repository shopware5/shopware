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
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.view.ArrayStoreField', {
    extend: 'Shopware.form.field.Grid',

    createItems: function() {
        var me = this;
        me.grid = me.createGrid();
        me.searchField = me.createSearchField();
        return [me.createToolbar(), me.searchField, me.grid];
    },

    getValue: function() {
        var me = this;
        var recordData = [];
        var store = me.store;

        store.each(function(item) {
            if (item.data.key.length <= 0 || item.data.value.length <= 0) {
                return;
            }
            recordData.push(item.data);
        });

        if (recordData.length <= 0) {
            return null;
        }

        return Ext.JSON.encode(recordData);
    },

    setValue: function(value) {
        var me = this;

        me.store.removeAll();
        if (!value) {
            return;
        }

        try {
            var records = Ext.JSON.decode(value);
        } catch (e) {
            return;
        }
        me.store.add(records);
    },

    createGrid: function() {
        var me = this;

        me.cellEditor = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1
        });

        return Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            store: me.store,
            border: false,
            flex: 1,
            plugins: [me.cellEditor],
            viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    ddGroup: 'shopware-form-field-grid' + this.id
                }
            },
            hideHeaders: true
        });
    },

    createColumns: function() {
        var me = this, columns = [];

        columns.push(me.createSortingColumn());
        columns.push({
            dataIndex: 'key',
            flex: 1,
            renderer: function(value) {
                if (value.length > 0) {
                    return value;
                }
                return '<span style="color: #c4c4c4">{s name="define_key"}{/s}</span>';
            },
            editor: { xtype: 'textfield', allowBlank: false, emptyText: '{s name="define_key"}{/s}' }
        });
        columns.push({
            dataIndex: 'value',
            flex: 2,
            renderer: function(value) {
                if (value.length > 0) {
                    return value;
                }
                return '<span style="color: #c4c4c4">{s name="define_value"}{/s}</span>';
            },
            editor: { xtype: 'textfield', allowBlank: false, emptyText: '{s name="define_value"}{/s}' }
        });

        columns.push(me.createActionColumn());
        return columns;
    },

    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            style: {
                'border': '1px solid #c4c4c4',
                'padding-bottom': '2px',
                'border-bottom': '0 none'
            },
            items: [{
                xtype: 'button',
                cls: 'secondary small',
                iconCls: 'sprite-plus-circle-frame',
                text: '{s namespace="backend/application/main" name="grid_panel/add_button_text"}{/s}',
                handler: function() {
                    var store = me.grid.getStore();
                    store.add({ key: '', value: '' });
                    var record = store.getAt(store.getCount() - 1);
                    me.cellEditor.startEdit(record, 1);
                }
            }]
        });
    },

    createSearchField: function() {
        return null;
    },

    initializeStore: function() {
        return this.store;
    }
});
