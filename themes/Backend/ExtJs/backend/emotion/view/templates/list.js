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

//{namespace name=backend/emotion/templates/list}

/**
 * Shopware UI - Emotion Toolbar
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/grids/list"}
Ext.define('Shopware.apps.Emotion.view.templates.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.emotion-templates-list',

    /**
     * Snippets which are used by this component.
     * @Object
     */
    snippets: {
        invalid_template: '{s name=templates/error/invalid_template}The provided file seems to be not a valid template file{/s}',
        columns: {
            name: '{s name=templates/list/columns/name}Name{/s}',
            file: '{s name=templates/list/columns/file}Template file{/s}',
            actions: '{s name=grids/list/columns/actions}Action(s){/s}'
        },
        tooltips: {
            edit: '{s name=grids/list/tooltip/edit}Edit{/s}',
            duplicate: '{s name=grids/list/tooltip/duplicate}Duplicate{/s}',
            remove: '{s name=grids/list/tooltip/remove}Delete{/s}'
        }
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return { Void }
     */
    initComponent: function() {
        var me = this;

        me.addEvents('selectionChange', 'editEntry', 'duplicate', 'remove');

        me.store = Ext.create('Shopware.apps.Emotion.store.Templates').load();
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
            dataIndex: 'file',
            header: me.snippets.columns.file,
            flex: 1,
            renderer: me.fileRenderer,
            editor: {
                xtype: 'textfield',
                allowBlank: false,
                validator: function(value) {
                    return (/^((.*)\.tpl)$/.test(value)) ? true : me.snippets.invalid_template;
                }
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
                iconCls: 'sprite-duplicate-template',
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
                    if (record.get('id') < 2) {
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
     * Column renderer for the `file` column.
     *
     * @param { String } value - The column content
     * @returns { String } formatted output
     */
    fileRenderer: function(value) {
        if(!value) {
            return 'index.tpl'
        }
        return value;
    }
});
//{/block}
