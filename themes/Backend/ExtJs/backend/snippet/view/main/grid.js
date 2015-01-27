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

//{namespace name=backend/snippet/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/snippet/view/main/grid"}
Ext.define('Shopware.apps.Snippet.view.main.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.snippet-main-grid',
    enableColumnHide: false,

    /**
     * @object [Shopware.apps.Snippet.model.Shoplocale]
     */
    shoplocale: null,

    /**
     * @boolean
     */
    isExpertMode: false,

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        tooltipTranslateSnippet:   '{s name=tooltip_translate_snippet}Translate this snippet{/s}',
        tooltipDeleteSnippet: '{s name=tooltip_delete_snippet}Delete this snippet{/s}',

        columnNamespace: '{s name=column_namespace}Namespace{/s}',
        columnName:      '{s name=column_name}Name{/s}',
        columnValue:     '{s name=column_value}Value{/s}',

        buttonFilterEmpty:  '{s name=button_filter_empty}Show only empty snippets{/s}',
        buttonEditSelected: '{s name=button_edit_selected}Edit selected snippets{/s}',
        buttonAddSnippet:   '{s name=button_add_snippet}Add snippet{/s}',

        emptyTextSearch:   '{s name=empty_text_search}search...{/s}'
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
             * @event deleteSingle
             * @param [object] grid - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             */
            'deleteSingle',

            /**
             * Event will be fired when the user clicks the edit selected snippets button
             *
             * @event editSelectedSnippets
             * @param [array]
             */
            'editSelectedSnippets',

            /**
             * Event will be fired when the user clicks the translate button for a snippet
             *
             * @event translateSnippet
             * @param [object] record
             */
            'translateSnippet'
        );
    },


    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.listeners = {
            beforeactivate: me.onBeforeActivate,
            beforeedit:     me.onBeforeEdit
        };

        me.editor   = me.getRowEditorPlugin();
        me.plugins  = [ me.editor ];
        me.selModel = me.getGridSelModel();
        me.columns  = me.getColumns();
        me.tbar     = me.getToolbar();
        me.bbar     = me.getPagingbar();

        me.registerEvents();

        me.callParent(arguments);
    },

    /**
     * @param boolean - enabled
     * @return void
     */
    enableExpertMode: function(enabled) {
        var me = this;

        me.isExpertMode = enabled;

        var actionColum = me.down('actioncolumn');
        if (enabled) {
            actionColum.width += 26;
        } else {
            actionColum.width -= 26;
        }

        if (me.isVisible()) {
            me.store.load();
        }
    },

    /**
     * Resets the filterEmpty-Button and the searchfield to default values
     *
     * @event beforeactivate
     */
    onBeforeActivate: function() {
        var me             = this,
            filterEmptyBtn = me.ownerCt.down('button[action=filterEmpty]'),
            searchField    = me.ownerCt.down('textfield[action=search]');

        searchField.setValue('');
        filterEmptyBtn.toggle(false, true);
    },

    /**
     * Event will be fired when the user start the editing
     *
     * @param [Ext.grid.plugin.Editing] - The row editor
     * @return void
     */
    onBeforeEdit: function(editor) {
        var me             = this,
            rowEditingForm = editor.editor.form;

        if (me.isExpertMode) {
            rowEditingForm.findField('namespace').enable();
            rowEditingForm.findField('name').enable();
        } else {
            rowEditingForm.findField('namespace').disable();
            rowEditingForm.findField('name').disable();
        }
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me               = this,
            actionColumnItems = [];

        /*{if {acl_is_allowed privilege=update}}*/
        actionColumnItems.push({
                action: 'translate',
                cls: 'translateBtn',
                iconCls: 'sprite-globe',
                tooltip: me.snippets.tooltipTranslateSnippet,
                handler: function(grid, rowIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.fireEvent('translateSnippet', record);
                }
            }
        );
        /*{/if}*/

        /*{if {acl_is_allowed privilege=delete}}*/
        actionColumnItems.push({
            action: 'delete',
            cls: 'deleteBtn',
            hideMode: 'display',
            iconCls: 'sprite-minus-circle-frame',
            tooltip:  me.snippets.tooltipDeleteSnippet,
            handler: function(grid, rowIndex, colIndex) {
                me.fireEvent('deleteSingle', grid, rowIndex, colIndex);
            },
            getClass: function() {
                if (!me.isExpertMode)  {
                    return 'x-hidden';
                }
            }
        });
        /*{/if}*/

        var columns = [{
            header:  me.snippets.columnNamespace,
            dataIndex: 'namespace',
            flex: 1,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        },{
            header: me.snippets.columnName,
            dataIndex: 'name',
            flex: 1,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }, {
            header: me.snippets.columnValue,
            dataIndex: 'value',
            flex: 2,
            editor: {
                xtype: 'textfield'
            }
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: 26,
            items: actionColumnItems
        }];

        return columns;
    },

    /**
     * Creates rowEditor Plugin
     *
     * @return [Ext.grid.plugin.RowEditing]
     */
    getRowEditorPlugin: function() {
        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            errorSummary: false,
            pluginId: 'rowEditing'
        });
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel: function () {
        return Ext.create('Ext.selection.CheckboxModel');
    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function () {
        return Ext.create('Ext.toolbar.Paging', {
            store: this.store,
            dock:'bottom',
            displayInfo: true
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

        buttons.push({
            xtype: 'button',
            text: me.snippets.buttonFilterEmpty,
            enableToggle: true,
            action : 'filterEmpty',
            iconCls: 'sprite-blue-document-template'
        });

        /*{if {acl_is_allowed privilege=update}}*/
        buttons.push({
            xtype: 'button',
            text: me.snippets.buttonEditSelected,
            iconCls: 'sprite-pencil',
            handler: function() {
                var selection = me.selModel.getSelection();

                if (selection.length === 0) {
                    me.selModel.selectAll();
                    selection = me.selModel.getSelection();
                }

                if (selection.length === 0) {
                    return;
                }

                me.fireEvent('editSelectedSnippets', selection);
            }
        });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=create}}*/
        buttons.push({
            xtype: 'button',
            iconCls: 'sprite-plus-circle',
            text: me.snippets.buttonAddSnippet,
            action: 'add-snippet'
        });
        /*{/if}*/

        buttons.push({
            xtype: 'tbfill'
        });

        buttons.push({
            xtype : 'textfield',
            name : 'searchfield',
            action : 'search',
            width: 170,
            cls: 'searchfield',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            emptyText : me.snippets.emptyTextSearch
        });

        buttons.push({
            xtype: 'tbspacer',
            width: 6
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    }
});
//{/block}
