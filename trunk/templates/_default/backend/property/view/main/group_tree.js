/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/property/view/main}
//{block name="backend/property/view/main/group_tree"}
Ext.define('Shopware.apps.Property.view.main.GroupTree', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.property-main-groupTree',

    displayField: 'name',
    useArrows: false,
    rootVisible: false,
    sortableColumns: false,

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        columnGroup:      '{s name=group/column_group}Group{/s}',
        columnComparable: '{s name=group/column_comparable}Comparable{/s}',
        columnSort:       '{s name=group/column_sort}Sort{/s}',
        columnPosition:   '{s name=group/column_position}Position{/s}',

        buttonAddGroup: '{s name=group/button_add_group}Add group{/s}',

        tooltipDeleteGroup:           '{s name=group/tooltip_delete_group}Delete group{/s}',
        tooltipRemoveOptionFromGroup: '{s name=group/tooltip_remove_option_from_group}Remove option from group{/s}',

        comboSortModeAlphabetical: '{s name=group/cobo_sort_mode_alphabetical}Alphabetical{/s}',
        comboSortModeNumeric:      '{s name=group/cobo_sort_mode_numeric}Numeric{/s}',
        comboSortModeNumber:       '{s name=group/cobo_sort_mode_number}Number{/s}',
        comboSortModePosition:     '{s name=group/cobo_sort_mode_postition}Position{/s}'
    },

    /**
     * Configure the root node of the tree panel. This is necessary
     * due to the fact that the ExtJS 4.0.7 build fires the load
     * event to often if no root node is configured.
     *
     * @object
     */
    root: {
        name: 'Groups',
        expanded: true
    },

    viewConfig:{
        markDirty:false
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
             * @event deleteGroup
             * @param [object] node - Ext.data.NodeInterface
             * @param [object] tree - Associated Ext.tree.ViewView
             */
            'deleteGroup',

            /**
             * Event will be fired when the user clicks the delete icon in the
             * toolbar
             *
             * @event removeOptionFromGroup
             * @param [object] node - Ext.data.NodeInterface
             * @param [object] tree - Associated  Ext.tree.ViewView
             */
            'removeOptionFromGroup',

            /**
             * @event addOptionToGroup
             * @param [object] tree -  Associated  Ext.tree.ViewView
             * @param [object] group -  Shopware.apps.Property.model.Group
             * @param [object] option -  Associated  Ext.tree.ViewView
             * @param [object] child - Shopware.apps.Property.model.Group
             */
            'addOptionToGroup'
        );
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.registerEvents();
        me.store = me.groupStore;

        me.sortModeEditor = Ext.create('Ext.form.field.ComboBox', {
            store: new Ext.data.SimpleStore({
                fields:['id', 'label'],
                data: [
                    [0, me.snippets.comboSortModeAlphabetical],
                    [1, me.snippets.comboSortModeNumeric],
                    [2, me.snippets.comboSortModeNumber],
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

//        me.viewConfig = me.getViewConfig();

        me.editor  = me.getRowEditorPlugin();
        me.plugins = [ me.getGridTranslationPlugin(), me.editor ];
        me.tbar    = me.getToolbar();
        me.columns = me.getColumns();

        me.callParent(arguments);
    },

    /**
     * Creates gridviewdragdrop plugin
     *
     * @return [object]
     */
    getViewConfig: function() {
        var viewConfig = {
            plugins: {
                ptype: 'treeviewdragdrop',
                dragGroup: 'groupGridDDGroup',
                dropGroup: 'groupGridDDGroup'
            }
        };

        return viewConfig;
    },

    /**
     * Creates new Grid-Translation Plugin
     *
     * @return [Shopware.grid.plugin.Translation]
     */
    getGridTranslationPlugin: function() {
        return Ext.create('Shopware.grid.plugin.Translation', {
            translationType: 'propertygroup',
            actionColumnItemGetClassCallback: function(value, metadata, record) {
                if (record.get('isOption'))  {
                    return 'x-hidden';
                }
            }
        });
    },

    /**
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
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        return [{
            xtype: 'treecolumn',
            text: me.snippets.columnGroup,
            flex: 2,
            sortable: true,
            dataIndex: 'name',
            translationEditor: {
                xtype: 'textfield',
                name: 'groupName',
                fieldLabel: 'groupName',
                allowBlank: false
            },
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
            width: 32,
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
                    me.fireEvent('deleteGroup', node, tree);
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
            }]
        }];

    },

    /**
     * Creates rowEditor Plugin
     *
     * @return [Ext.grid.plugin.RowEditing]
     */
    getRowEditorPlugin: function() {
        var me = this;

        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            errorSummary: false,
            pluginId: 'rowEditing',
            listeners: {
                canceledit: {
                    scope: me,
                    fn: function(editor, event) {
                        var me     = this,
                            store  = me.store;

                        store.getRootNode().eachChild(function(node) {
                            if (node.phantom) {
                                node.remove();
                            }
                        });

                        // enable add button
                        me.addBtn.enable();
                    }
                },
                beforeedit: {
                    scope: me,
                    fn: function(editor, event) {
                        var ed = editor.editor,
                            name = ed.items.getAt(0),
                            compare = ed.items.getAt(1),
                            sort = ed.items.getAt(3);

                        // Disable row editor on option-nodes
                        if (event && event.record.get('isOption')) {
                            name.setDisabled(true);
                            compare.setDisabled(true);
                            sort.setDisabled(true);
                        } else {
                            name.setDisabled(false);
                            compare.setDisabled(false);
                            sort.setDisabled(false);
                        }

                        var store = me.store;
                        store.getRootNode().eachChild(function(node) {
                            if (node.phantom && node !== event.record) {
                                node.remove();
                            }
                        });
                    }
                }
            }
        });
    },

    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        var buttons = [];

        me.addBtn = Ext.create('Ext.button.Button', {
            xtype: 'button',
            text: me.snippets.buttonAddGroup,
            iconCls: 'sprite-plus-circle-frame',
            action: 'add',
            handler: function() {
                var newRecord = Ext.create('Shopware.apps.Property.model.Group');

                me.getRootNode().appendChild(newRecord);

                this.disable();
                me.editor.startEdit(newRecord, 0);
            }
        });

        buttons.push(me.addBtn);

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    },

    afterRender: function () {
        var me = this, view = me.getView();
        me.callParent(arguments);

        me.dropZone = new Ext.dd.DropZone(view.getEl(), {

            ddGroup: 'option-group',

            // If the mouse is over a grid row, return that node. This is
            // provided as the "target" parameter in all "onNodeXXXX" node event handling functions
            getTargetFromEvent: function (e) {
                return e.getTarget(me.getView().rowSelector);
            },

            // While over a target node, return the default drop allowed class which
            // places a "tick" icon into the drag proxy.
            onNodeOver: function (target, dd, e, data) {
                var groupModel   = view.getRecord(target),
                    optionRecord = data.records[0];

                if (groupModel.get('isOption') || this.isAlreadyInserted(groupModel, optionRecord)) {
                    return Ext.dd.DropZone.prototype.dropNotAllowed;
                } else {
                    return Ext.dd.DropZone.prototype.dropAllowed;
                }
            },

            // On node drop we can interrogate the target to find the underlying
            // application object that is the real target of the dragged data.
            // In this case, it is a Record in the GridPanel's Store.
            // We can use the data set up by the DragZone's getDragData method to read
            // any data we decided to attach in the DragZone's getDragData method.
            onNodeDrop: function (target, dd, e, data) {
                var groupModel   = view.getRecord(target),
                    optionRecord = data.records[0];

                if (groupModel.get('isOption') != false) return false;

                if (this.isAlreadyInserted(groupModel, optionRecord)) return false;


                var child = Ext.create('Shopware.apps.Property.model.Group', {
                    id: groupModel.get('id') + '_' + optionRecord.data.id,
                    name: optionRecord.data.name,
                    leaf: true,
                    isOption: true
                });
                groupModel.appendChild(child);
                groupModel.expand();

                me.fireEvent('addOptionToGroup', me, groupModel, optionRecord, child);

                return true;
            },

            /**
             *
             * @param [object] groupModel -  Shopware.apps.Property.model.Group
             * @param [object] optionRecord - Shopware.apps.Property.model.FilterOption
             * @return [boolean]
             */
            isAlreadyInserted: function (groupModel, optionRecord) {
                var inserted = false,
                    optionId = groupModel.get('id') + '_' + optionRecord.data.id;

                if (groupModel.hasChildNodes()) {
                    groupModel.eachChild(function (child) {
                        if (child.get('id') === optionId) {
                            inserted = true;
                        }
                    });
                }

                return inserted;
            }
        });
    }
});
//{/block}
