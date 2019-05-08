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

// {namespace name="backend/content_type_manager/main"}
// {block name="backend/content_type_manager/view/field_handler/combobox_handler"}
Ext.define('Shopware.apps.ContentTypeManager.view.field_handler.ComboboxHandler', {
    is: function (typeName) {
        return typeName === 'combobox';
    },

    getFields: function () {
        var me = this;

        this.rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToMoveEditor: 1,
            autoCancel: false
        });

        this.grid = Ext.create('Ext.grid.Panel', {
            name: 'store',
            minHeight: 200,
            title: '{s name="handler_combobox/title"}{/s}',
            tbar: this.createTopToolbar(),
            plugins: [this.rowEditing],
            columns: [
                {
                    header: '{s name="handler_combobox/label"}{/s}',
                    dataIndex: 'name',
                    flex: 1,
                    editor: {
                        xtype: 'textfield'
                    }
                },
                {
                    xtype: 'actioncolumn',
                    width: 30,
                    items: [
                        {

                            iconCls: 'sprite-minus-circle-frame',
                            handler: function (view, rowIndex, colIndex, item, opts, record) {
                                me.grid.store.remove(record);
                            }
                        }
                    ]
                }
            ],
            store: Ext.create('Ext.data.Store', {
                fields: ['name']
            }),
            getName: function() {
                return 'store';
            },
            setValue: function (values) {
                if (!Ext.isDefined(values.forEach)) {
                    return;
                }

                values.forEach(function (value) {
                    me.grid.store.add(value)
                })
            },
            getValue: function () {
                var result = [];
                me.grid.store.each(function (record) {
                    result.push({
                        id: record.get('name'),
                        name: record.get('name'),
                    })
                });

                return result;
            }
        });

        return [
            this.grid
        ];
    },

    createTopToolbar: function() {
        var me = this;

        me.createButton = Ext.create('Ext.button.Button', {
            text: '{s name="handler_combobox/newOption"}{/s}',
            iconCls: 'sprite-plus-circle-frame',
            handler: function () {
                me.grid.store.add({});
                var record = me.grid.store.getAt(me.grid.store.count() -1);
                me.rowEditing.startEdit(record, 0);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [me.createButton]
        });
    },
});
// {/block}
