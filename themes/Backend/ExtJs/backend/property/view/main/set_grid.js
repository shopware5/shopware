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
 * @package    Snippet
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/property/view/main}
//{block name="backend/property/view/main/set_grid"}
Ext.define('Shopware.apps.Property.view.main.SetGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.property-main-setGrid',
    addBtn: null,

    title: '{s name=set/grid_title}Sets{/s}',
    sortableColumns: false,

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        columnGroup:      '{s name=set/column_set}Group{/s}',
        columnComparable: '{s name=set/column_comparable}Comparable{/s}',
        columnSort:       '{s name=set/column_sort}Sort{/s}',
        columnPosition:   '{s name=set/column_position}Position{/s}',

        buttonAddSet: '{s name=set/button_add_set}Add set{/s}',

        tooltipDeleteGroup:           '{s name=set/tooltip_delete_set}Delete set{/s}',
        tooltipRemoveOptionFromGroup: '{s name=set/tooltip_remove_option_from_set}Remove option from set{/s}',

        comboSortModeAlphabetical:  '{s name=set/cobo_sort_mode_alphabetical}Alphabetical{/s}',
        comboSortModeNumeric:       '{s name=set/cobo_sort_mode_numeric}Numeric{/s}',
        comboSortModePosition:      '{s name=set/cobo_sort_mode_postition}Position{/s}',
        search:                     '{s name=set/empty_text_search}Search...{/s}'
    },

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.registerEvents();


        me.sortModeEditor = Ext.create('Ext.form.field.ComboBox', {
            store: new Ext.data.SimpleStore({
                fields:['id', 'label'],
                data: [
                    [0, me.snippets.comboSortModeAlphabetical],
                    [1, me.snippets.comboSortModeNumeric],
                    [3, me.snippets.comboSortModePosition]
                ]
            }),
            allowBlank: false,
            editable: false,
            mode: 'local',
            triggerAction: 'all',
            displayField: 'label',
            valueField: 'id'
        });


        me.store = me.setStore;

        me.store.load({
            callback: function () {
                if (me.store.count() > 0) {
                    me.getSelectionModel().select(0);
                }
            }
        });

        me.editor = me.getRowEditorPlugin();
        me.plugins = [me.editor];
        me.tbar = me.getToolbar();
        me.columns = me.getColumns();
        me.dockedItems = [ me.getPagingBar() ];
        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
                /**
                 * Event will be fired when the user clicks the delete icon in the
                 * action column
                 *
                 * @event deleteOption
                 * @param [object] record
                 * @param [object] grid - Associated Ext.view.Table
                 */
                'deleteOption'
        );
    },

    /**
     * Creates editor Plugin
     *
     * @return [Ext.grid.plugin.RowEditing]
     */
    getRowEditorPlugin: function () {
        var me = this;

        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            errorSummary: false,
            pluginId: 'rowEditing',
            listeners: {
                canceledit: {
                    scope: me,
                    fn: function (editor, event) {
                        var me = this,
                                store = me.store;

                        store.each(function (record) {
                            if (record.phantom) {
                                store.remove(record);
                            }
                        });

                        // enable add button
                        me.addBtn.enable();
                    }
                },
                beforeedit: {
                    scope: me,
                    fn: function (editor, event) {
                        var store = me.store;
                        store.each(function (record) {
                            if (record.phantom && record !== event.record) {
                                store.remove(record);
                            }
                        });
                    }
                }
            }
        });
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        return [{
            text: me.snippets.columnGroup,
            flex: 2,
            sortable: false,
            dataIndex: 'name',
            renderer: 'htmlEncode',
            editor: {
                allowBlank: false
            }
        }, {
            text: me.snippets.columnComparable,
            flex: 1,
            dataIndex: 'comparable',
            xtype: 'booleancolumn',
            renderer: function(value, metaData, record) {
                var me = this;

                if (record.get('isOption')) {
                    return '';
                }

                var boolColumn = Ext.create('Ext.grid.column.Boolean');
                return boolColumn.renderer(value);
            },
            editor: {
                xtype: 'checkbox',
                inputValue: true,
                uncheckedValue: false
            }
        }, {
            text: me.snippets.columnPosition,
            dataIndex: 'position',
            flex: 1,
            editor: {
                xtype: 'numberfield',
                minValue: 0
            }
        }, {
            text: me.snippets.columnSort,
            flex: 1,
            renderer: me.sortModeRenderer,
            dataIndex: 'sortMode',
            editor: me.sortModeEditor
        }, {
            xtype: 'actioncolumn',
            width: 60,
            hideable: false,
            items: [{
                iconCls: 'sprite-minus-circle-frame',
                action: 'delete',
                cls: 'delete',
                tooltip: me.snippets.tooltipDeleteGroup,
                getClass: function(value, metadata, record) {
                    if (record.get('isOption'))  {
                        return 'x-hidden';
                    }
                },

                handler: function(tree, rowIndex) {
                    var node  = tree.getStore().getAt(rowIndex);
                    me.fireEvent('deleteSet', node, tree);
                }
            }, {
                iconCls: 'sprite-minus-circle-frame',
                action: 'delete',
                cls: 'delete',
                tooltip: me.snippets.tooltipRemoveOptionFromGroup,
                getClass: function(value, metadata, record) {
                    if (!record.get('isOption'))  {
                        return 'x-hidden';
                    }
                },

                handler: function(tree, rowIndex) {
                    var node = tree.getStore().getAt(rowIndex);
                    me.fireEvent('removeOptionFromGroup', node, tree);
                }
            }, {
                iconCls: 'sprite-pencil',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('editSet', record);
                }
            }]
        }];
    },


    /**
    * Creates the paging toolbar for the grid to allow
    * and store paging. The paging toolbar uses the same store as the Grid
    *
    * @return Ext.toolbar.Paging The paging toolbar for the customer grid
    */
    getPagingBar: function () {
        var me = this;
        return Ext.create('Ext.toolbar.Paging', {
            store:me.store,
            dock:'bottom',
            displayInfo:true
        });

    },

    /**
     * renderer for the sort mode
     *
     * @param [object] - value
     * @param [object] - metaData
     * @param [object] - record
     * @param [number] - rowIndex
     * @param [number] - colIndex
     * @param [object] - store
     * @param [object] - view
     * @return [string]
     */
    sortModeRenderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
        var me = this;

        if (record.get('isOption')) {
            return '';
        }

        var index  = me.sortModeEditor.store.find(this.sortModeEditor.valueField, value);

        if (index === -1) {
            index = 0;
        }

        record = me.sortModeEditor.store.getAt(index);

        return record.get(this.sortModeEditor.displayField);
    },

    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function () {
        var me = this,
                items = [];

        me.addBtn = Ext.create('Ext.button.Button', {
            xtype: 'button',
            text: me.snippets.buttonAddSet,
            iconCls: 'sprite-plus-circle-frame',
            action: 'add',
            handler: function () {
                var newRecord = Ext.create('Shopware.apps.Property.model.Set');

                this.disable();
                me.store.add(newRecord);
                me.editor.startEdit(newRecord, 0);
            }
        });

        items.push(me.addBtn);
        items.push(
                '->',
                {
                    xtype: 'textfield',
                    name: 'searchfield',
                    action: 'searchSets',
                    width: 100,
                    cls: 'searchfield',
                    enableKeyEvents: true,
                    checkChangeBuffer: 500,
                    emptyText: me.snippets.search
                }
        );

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: items
        });
    }
});
//{/block}
