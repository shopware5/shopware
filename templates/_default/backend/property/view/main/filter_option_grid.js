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
 * @package    Property
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/property/view/main}
//{block name="backend/Property/view/main/filter_option_grid"}
Ext.define('Shopware.apps.Property.view.main.FilterOptionGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.property-main-filterOptionGrid',
    addBtn: null,

    sortableColumns: false,

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        columnName:          '{s name=option/column_name}Name{/s}',
        columnFilterable:    '{s name=option/column_filterable}Filterable{/s}',
        tooltipDeleteOption: '{s name=option/tooltip_delete_value}Delete option{/s}',
        buttonAddOption:     '{s name=option/button_add_option}Add option{/s}',
        dragText:            '{s name=option/drag_text}Drag and drop to reorganize{/s}'
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
                ddGroup: 'option-group',
                dragText: me.snippets.dragText
            }
        };

        me.store = me.filterOptionStore;

        me.store.load({
            callback: function() {
                if (me.store.count() > 0) {
                    me.getSelectionModel().select(0);
                }
            }
        });

        me.editor   = me.getRowEditorPlugin();
        me.plugins    = [ me.getGridTranslationPlugin(), me.editor ];
        me.tbar     = me.getToolbar();
        me.columns  = me.getColumns();

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
             * @event deleteOption
             * @param [object] record
             * @param [object] grid - Associated Ext.view.Table
             */
            'deleteOption'
        );
    },

    /**
     * Creates new Grid-Translation Plugin
     *
     * @return [Shopware.grid.plugin.Translation]
     */
    getGridTranslationPlugin: function() {
        return Ext.create('Shopware.grid.plugin.Translation', {
            translationType: 'propertyoption'
        });
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
            translationEditor: {
                xtype: 'textfield',
                name: 'optionName',
                fieldLabel: 'option',
                allowBlank: false
            },
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
            width: 24,
            hideable: false,
            items: [{
                iconCls: 'sprite-minus-circle-frame',
                action: 'delete',
                cls: 'delete',
                tooltip: me.snippets.tooltipDeleteOption,
                handler: function(grid, rowIndex) {
                    var record  = grid.getStore().getAt(rowIndex);

                    me.fireEvent('deleteOption', record, grid);
                }
            }]
        }];

        return columns;
    },

    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me      = this,
            buttons = [];

        me.addBtn = Ext.create('Ext.button.Button', {
            xtype: 'button',
            text: me.snippets.buttonAddOption,
            iconCls: 'sprite-plus-circle-frame',
            action: 'add',
            handler: function() {
                var newRecord = Ext.create('Shopware.apps.Property.model.FilterOption');

                this.disable();
                me.store.add(newRecord);
                me.editor.startEdit(newRecord, 0);
            }
        });

        buttons.push(me.addBtn);

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    }
});
//{/block}
