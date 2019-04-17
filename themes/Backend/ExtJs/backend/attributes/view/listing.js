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

Ext.define('Shopware.apps.Attributes.view.Listing', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.attributes-listing',

    configure: function() {
        return {
            searchField: false,
            editColumn: false,
            pageSize: false,
            deleteButton: false,

            /*{if !{acl_is_allowed privilege=update}}*/
                addButton: false,
            /*{/if}*/

            columns: {
                columnName: '{s name="column_name"}{/s}',
                columnType: {
                    header: '{s name="column_type"}{/s}',
                    renderer: this.typeRenderer
                },
                sqlType: {
                    header: '{s name="sql_type"}{/s}',
                    renderer: this.sqlTypeRenderer
                },
                label: '{s name="label"}{/s}',
                translatable: '{s name="translatable"}{/s}',
                displayInBackend: {
                    header: '{s name="display_in_backend"}{/s}',
                    width: 120
                },
                position: {
                    width: 90,
                    header: '{s name="position"}{/s}'
                }
            }
        };
    },

    initComponent: function() {
        this.typeStore = Ext.create('Shopware.apps.Attributes.store.Types').load();
        return this.callParent(arguments);
    },

    createDeleteColumn: function() {
        var me = this,
            column = me.callParent(arguments);

        column.getClass = function(value, metaData, record) {
            /*{if !{acl_is_allowed privilege=update}}*/
                return 'x-hidden';
            /*{/if}*/

            if (!record.allowDelete()) {
                return 'x-hidden';
            }
        };

        column.handler = function (view, rowIndex, colIndex, item, opts, record) {
            me.fireEvent('delete-attribute-column', record);
        };

        return column;
    },

    createSelectionModel: function () {
        var me = this;
        return Ext.create('Ext.selection.RowModel', {
            listeners: {
                selectionchange: function (selModel, selection) {
                    return me.fireEvent(me.eventAlias + '-selection-changed', me, selModel, selection);
                }
            }
        });
    },

    createFeatures: function () {
        var me = this;

        me.groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: new Ext.XTemplate(
                '{literal}{name:this.formatName} ({rows.length}{/literal} {s name="grouping_suffix"}{/s})',
                {
                    formatName: function(value) {
                        switch(value) {
                            case true:
                                return '{s name="grouping_configured"}{/s}';
                            case false:
                                return '{s name="grouping_not_configured"}{/s}';
                        }
                    }
                }
            )
        });

        return me.groupingFeature;
    },

    typeRenderer: function(value, metaData, record) {
        this.typeStore.each(function(item, index) {
            if (item.get('unified') == value) {
                value = item.getLabel();
            }
        });

        return value;
    },

    sqlTypeRenderer: function(value) {
        return '<i>' + value + '</i>';
    },

    createToolbarItems: function() {
        var me = this, items = me.callParent(arguments);

        me.generateModelButton = Ext.create('Ext.button.Button', {
            text: '{s name="generate_model_button"}{/s}',
            iconCls: 'sprite-arrow-circle',
            handler: function() {
                me.fireEvent('generate-model');
            }
        });

        /*{if {acl_is_allowed privilege=update}}*/
            items.push(me.generateModelButton);
        /*{/if}*/

        me.tableComboBox = me.createTableComboBox();
        items.push({ xtype: 'tbspacer', width: 15 });
        items.push(me.tableComboBox);

        return items;
    },

    createTableComboBox: function() {
        var me = this;

        me.tableStore = Ext.create('Shopware.apps.Attributes.store.Table');
        me.tableStore.load();

        return Ext.create('Ext.form.field.ComboBox', {
            store: me.tableStore,
            displayField: 'label',
            fieldLabel: '{s name="attribute_table"}{/s}',
            forceSelection: true,
            editable: false,
            valueField: 'name',
            flex: 1,
            value: me.table,
            emptyText: '{s name="table_combo_empty_text"}{/s}',
            listConfig: {
                getInnerTpl: function() {
                    return '{literal}' +
                        '<tpl if="values.label">' +
                            '<b>{label}</b>&nbsp;<i>({name})</i>' +
                        '<tpl else>' +
                            '<b>{name}</b>' +
                        '</tpl>' +
                    '{/literal}'
                }
            },
            displayTpl: Ext.create('Ext.XTemplate',
                '{literal}<tpl for=".">' +
                '<tpl if="values.label">' +
                    '{label} ({name})' +
                '<tpl else>' +
                    '{name}' +
                '</tpl>' +
                '</tpl>{/literal}'
            ),
            listeners: {
                change: function(combo, value) {
                    me.fireEvent('display-table-columns', value);
                }
            }
        });
    }
});
