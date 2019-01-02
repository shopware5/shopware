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
 * @package    Property
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/property/view/main}
//{block name="backend/Property/view/main/group_grid"}
Ext.define('Shopware.apps.Property.view.main.GroupGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.property-main-groupGrid',
    addBtn: null,

    title: '{s name=group/grid_title}All Groups{/s}',
    sortableColumns: false,

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        columnName:          '{s name=group/column_name}Name{/s}',
        columnFilterable:    '{s name=group/column_filterable}Filterable{/s}',
        tooltipDeleteGroup: '{s name=group/tooltip_delete_value}Delete group{/s}',
        buttonAddGroup:     '{s name=group/button_add_group}Add group{/s}',
        search:             '{s name=group/empty_text_search}Search...{/s}',
        dragText:            '{s name=group/drag_text}Drag and drop to reorganize{/s}'
    },

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.viewConfig = {
            plugins: {
                ptype: 'gridviewdragdrop',
                ddGroup: 'set-assignment-grid-dd',
                dragText: me.snippets.dragText,
                enableDrop: false
            }
        };

        me.store = me.groupStore;

        me.store.load({
            callback: function() {
                if (me.store.count() > 0) {
                    me.getSelectionModel().select(0);
                }
            }
        });

        me.editor   = me.getRowEditorPlugin();
        me.plugins  = [me.editor];
        me.tbar     = me.getToolbar();
        me.columns  = me.getColumns();
        me.dockedItems = [ me.getPagingBar() ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the delete icon in the
             * action column
             *
             * @event deleteGroup
             * @param [object] record
             * @param [object] grid - Associated Ext.view.Table
             */
            'deleteGroup'
        );
    },

    /**
     * Creates editor Plugin
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
                        var me    = this,
                            store = me.store;

                        store.each(function(record) {
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
                    fn: function(editor, event) {
                        var store = me.store;
                        store.each(function(record) {
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

        var columns = [{
            header: me.snippets.columnName,
            dataIndex: 'name',
            flex: 2,
            renderer: 'htmlEncode',
            editor: {
                allowBlank: false
            }
        }, {
            header: me.snippets.columnFilterable,
            dataIndex: 'filterable',
            flex: 1,
            xtype: 'booleancolumn',
            editor: {
                xtype: 'checkbox',
                inputValue: true,
                uncheckedValue: false
            }
        }, {
            xtype: 'actioncolumn',
            width: 60,
            hideable: false,
            items: [{
                iconCls: 'sprite-minus-circle-frame',
                action: 'delete',
                cls: 'delete',
                tooltip: me.snippets.tooltipDeleteGroup,
                handler: function(grid, rowIndex) {
                    var record  = grid.getStore().getAt(rowIndex);

                    me.fireEvent('deleteGroup', record, grid);
                }
            }, {
                iconCls: 'sprite-pencil',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('editGroup', record);
                }
            }]
        }];

        return columns;
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
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me      = this,
            items = [];

        me.addBtn = Ext.create('Ext.button.Button', {
            xtype: 'button',
            text: me.snippets.buttonAddGroup,
            iconCls: 'sprite-plus-circle-frame',
            action: 'add',
            handler: function() {
                var newRecord = Ext.create('Shopware.apps.Property.model.Group');

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
                action: 'searchGroups',
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
