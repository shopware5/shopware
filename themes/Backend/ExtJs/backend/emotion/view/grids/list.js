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
 * @package    Emotion
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/grids/list}

/**
 * Shopware UI - Emotion Toolbar
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/grids/list"}
Ext.define('Shopware.apps.Emotion.view.grids.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.emotion-grids-list',

    /**
     * Snippets which are used by this component.
     * @Object
     */
    snippets: {
        columns: {
            name: '{s name=grids/list/columns/name}Name{/s}',
            cols: '{s name=grids/list/columns/cols}Column(s){/s}',
            rows: '{s name=grids/list/columns/rows}Row(s){/s}',
            cellHeight: '{s name=grids/list/columns/cellHeight}Cell height (in px){/s}',
            articleHeight: '{s name=grids/list/columns/articleHeight}Article element height{/s}',
            gutter: '{s name=grids/list/columns/gutter}Gutter{/s}',
            actions: '{s name=grids/list/columns/actions}Action(s){/s}'
        },
        tooltips: {
            edit: '{s name=grids/list/tooltip/edit}Edit{/s}',
            duplicate: '{s name=grids/list/tooltip/duplicate}Duplicate{/s}',
            remove: '{s name=grids/list/tooltip/remove}Delete{/s}'
        },
        renderer: {
            articleHeight: '{s name=grids/list/renderer/articleHeight}cell(s){/s}'
        }
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @returns { Void }
     */
    initComponent: function() {
        var me = this;

        me.addEvents('selectionChange', 'editEntry', 'duplicate', 'remove');

        me.columns = me.createColumns();
        me.selModel = me.createSelectionModel();
        me.plugins = [ me.createEditor() ];
        me.bbar = me.createPagingToolbar();

        me.callParent(arguments);
    },

    /**
     * Creates the column model for the grid panel
     *
     * @returns { Array } columns
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'name',
            header: me.snippets.columns.name,
            flex: 1,
            renderer: me.nameRenderer,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }, {
            dataIndex: 'cols',
            header: me.snippets.columns.cols,
            flex: 1,
            editor: {
                xtype: 'numberfield',
                allowBlank: false
            }
        }, {
            dataIndex: 'rows',
            header: me.snippets.columns.rows,
            flex: 1,
            editor: {
                xtype: 'numberfield',
                allowBlank: false
            }
        }, {
            dataIndex: 'cellHeight',
            header: me.snippets.columns.cellHeight,
            flex: 1,
            renderer: me.cellHeightRenderer,
            editor: {
                xtype: 'numberfield',
                allowBlank: false
            }
        }, {
            dataIndex: 'articleHeight',
            header: me.snippets.columns.articleHeight,
            flex: 1,
            renderer: me.articleHeightRenderer,
            editor: {
                xtype: 'numberfield',
                allowBlank: false
            }
        }, {
            dataIndex: 'gutter',
            header: me.snippets.columns.gutter,
            flex: 1,
            renderer: me.cellHeightRenderer,
            editor: {
                xtype: 'numberfield',
                allowBlank: false
            }
        }, {
            xtype: 'actioncolumn',
            header: me.snippets.columns.actions,
            width: 85,
            items: [{
                iconCls: 'sprite-pencil',
                tooltip: me.snippets.tooltips.edit,
                handler: function(grid, row, col) {
                    var rec = grid.getStore().getAt(row);
                    me.fireEvent('editEntry', grid, rec, row, col);
                }
            }, {
                iconCls: 'sprite-duplicate-grid',
                tooltip: me.snippets.tooltips.duplicate,
                handler: function(grid, row, col) {
                    var rec = grid.getStore().getAt(row);
                    me.fireEvent('duplicate', grid, rec, row, col);
                }
            }, {
                iconCls: 'sprite-minus-circle',
                tooltip: me.snippets.tooltips.remove,
                handler: function(grid, row, col) {
                    var rec = grid.getStore().getAt(row);
                    me.fireEvent('remove', grid, rec, row, col);
                },
                getClass: function(value, metadata, record) {
                    if (record.get('id') < 3)  {
                        return Ext.baseCSSPrefix + 'hidden';
                    }
                }
            }]
        }];
    },

    /**
     * Creates the selection model.
     *
     * @returns { Ext.selection.CheckboxModel }
     */
    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                selectionchange:function (sm, selections) {
                    me.fireEvent('selectionChange', selections);
                }
            }
        });
    },

    /**
     * Creates the row editor
     *
     * @returns { Ext.grid.plugin.RowEditing }
     */
    createEditor: function() {
        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2
        });
    },

    /**
     * Creates the paging toolbar at the bottom of the list.
     *
     * @returns { Ext.toolbar.Paging }
     */
    createPagingToolbar: function() {
        var me = this,
            toolbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            pageSize: 20
        });

        return toolbar;
    },

    /**
     * Column renderer for the `name` column.
     *
     * The method wraps the value in `strong`-tags.
     *
     * @param { String } value - The column content
     * @returns { String } formatted output
     */
    nameRenderer: function(value) {
        return Ext.String.format('<strong>[0]</strong>', value);
    },

    /**
     * Column renderer for the `cellHeight` column.
     *
     * The method appends an `px` to the incoming value.
     *
     * @param { String } value - The column content
     * @returns { String } formatted output
     */
    cellHeightRenderer: function(value) {
        return Ext.String.format('[0]px', value);
    },

    /**
     * Column renderer for the `articleHeight` column.
     *
     * The method appends a localized `cell` string to the incoming value.
     *
     * @param { String } value - The column content
     * @returns { String } formatted output
     */
    articleHeightRenderer: function(value) {
        return Ext.String.format('[0] [1]', value, this.snippets.renderer.articleHeight);
    },

    /**
     * Creates the row editor
     *
     * @returns { Ext.grid.plugin.RowEditing }
     */
    createEditor: function() {
        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2
        });
    },

    /**
     * Column renderer for the `name` column.
     *
     * The method wraps the value in `strong`-tags.
     *
     * @param { String } value - The column content
     * @returns { String } formatted output
     */
    nameRenderer: function(value) {
        return Ext.String.format('<strong>[0]</strong>', value);
    },

    /**
     * Column renderer for the `cellHeight` column.
     *
     * The method appends an `px` to the incoming value.
     *
     * @param { String } value - The column content
     * @returns { String } formatted output
     */
    cellHeightRenderer: function(value) {
        return Ext.String.format('[0]px', value);
    },

    /**
     * Column renderer for the `articleHeight` column.
     *
     * The method appends a localized `cell` string to the incoming value.
     *
     * @param { String } value - The column content
     * @returns { String } formatted output
     */
    articleHeightRenderer: function(value) {
        return Ext.String.format('[0] [1]', value, this.snippets.renderer.articleHeight);
    }
});
//{/block}
