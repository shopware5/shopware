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
 * @package    Ticket
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/ticket/main}
//{block name="backend/ticket/view/settings/types"}
Ext.define('Shopware.apps.Ticket.view.settings.Types', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.grid.Panel',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-settings-types',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-settings-types',

    /**
     * Disable the outer border of the component.
     * @integer
     */
    border: false,

    /**
     * Disable the inner border of the component.
     * @integer
     */
    bodyBorder: 0,

    /**
     * Title of the component.
     * @string
     */
    title: '{s name=settings/types_title}Ticket types{/s}',

    /**
     * Initialize the component
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('editType', 'deleteType', 'deleteTypes', 'selectionChange', 'addType', 'searchType');

        me.store = me.typesStore;
        me.columns = me.createColumns();
        me.selModel = me.createSelModel();
        me.bbar = me.createPagingToolbar();
        me.tbar = me.createActionToolbar();
        me.plugins = [ me.createCellEditor() ];

        me.callParent(arguments);
    },

    /**
     * Creates the column model for the grid.
     *
     * @public
     * @return [array] - Array of the columns
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'id',
            header: '{s name=settings/types/columns/id}#{/s}',
            width: 50,
            renderer: me.idRenderer
        }, {
            dataIndex: 'name',
            header: '{s name=settings/types/columns/name}Type name{/s}',
            flex: 1,
            renderer: me.nameRenderer,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }, {
            dataIndex: 'gridColor',
            header: '{s name=settings/types/columns/color}Grid color{/s}',
            flex: 1,
            renderer: me.colorRenderer,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }, {
            xtype: 'actioncolumn',
            header: '{s name=settings/types/columns/actions}Action(s){/s}',
            width: 90,
            items: [{
                iconCls: 'sprite-pencil',
                tooltip: '{s name=settings/types/columns/edit_tip}Edit ticket type{/s}',
                handler: function(view, rowIdx, colIdx, item, e, record) {
                    me.fireEvent('editType', view, record, rowIdx, colIdx, item, e);
                }
            }, {
                iconCls: 'sprite-minus-circle',
                tooltip: '{s name=settings/types/columns/delete_tip}Delete ticket type{/s}',
                handler: function(view, rowIdx, colIdx, item, e, record) {
                    Ext.MessageBox.confirm('{s name=window_title}Ticket system{/s}', '{s name=settings/types/delete_confirm}Are you sure to delete the selected ticket type(s)?{/s}', function(button) {
                        if(button != 'yes') {
                            return false;
                        }
                        me.fireEvent('deleteType', view, record, rowIdx, colIdx, item, e);
                    });

                }
            }]
        }];
    },

    /**
     * Renders the ticket type id with a prefix.
     *
     * @public
     * @param [string] value - Value of the column
     * @return [string] formatted id
     */
    idRenderer: function(value) {
        return '#' + value;
    },

    /**
     * Renders the ticket type name in "strong"-tags.
     *
     * @public
     * @param [string] value - Value of the column
     * @return [string] formatted name
     */
    nameRenderer: function(value) {
        return '<strong style="font-weight:bold;">' + value + '</strong>'
    },

    /**
     * Renders the grid color as a "div"-box and append the hex code-
     *
     * @public
     * @param [string] value - Value of the column
     * @return [string] formatted color
     */
    colorRenderer: function(value) {
        return '<div style="width:14px;height:14px;background-color: '+ value +';display:inline-block;vertical-align: middle"></div>&nbsp;' + value;
    },

    /**
     * Returns the paging toolbar which
     * is located under the grid.
     *
     * @public
     * @return [object] Ext.toolbar.Paging
     */
    createPagingToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            displayInfo:true
        });
    },

    /**
     * Creates the selection model which is used in this grid.
     *
     * @public
     * @return [object] Ext.selection.CheckboxModel
     */
    createSelModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    me.fireEvent('selectionChange', selections);
                }
            }
        });
    },

    /**
     * Returns the action toolbar which
     * is located above the grid.
     *
     * @public
     * @return [object] Ext.toolbar.Toolbar
     */
    createActionToolbar: function() {
        var me = this;

        me.deleteButton = Ext.create('Ext.button.Button', {
            text: '{s name=settings/types/toolbar/delete_marked}Delete marked{/s}',
            iconCls: 'sprite-minus-circle',
            disabled: true,
            handler: function(btn) {
                Ext.MessageBox.confirm('{s name=window_title}Ticket system{/s}', '{s name=settings/types/delete_confirm}Are you sure to delete the selected ticket type(s)?{/s}', function(button) {
                    if(button != 'yes') {
                        return false;
                    }
                    me.fireEvent('deleteTypes', btn, me);
                });
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [{
                text: '{s name=settings/types/toolbar/create}Create new type{/s}',
                iconCls: 'sprite-plus-circle',
                handler: function(btn) {
                    me.fireEvent('addType', btn, me);
                }
            }, me.deleteButton, '->', {
                xtype: 'textfield',
                name: 'search',
                cls: 'searchfield',
                emptyText: '{s name=settings/types/toolbar/search/empty_text}Search...{/s}',
                listeners: {
                    scope: me,
                    buffer: 500,
                    change: function(field, newValue, oldValue) {
                        me.fireEvent('searchType', field, newValue, oldValue, me);
                    }
                }
            }, ' ']
        });
    },

    /**
     * Returns the cell editor for the component.
     *
     * @public
     * @return [object] Ext.grid.plugin.CellEditing
     */
    createCellEditor: function() {
        return Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 2
        });
    }
});
//{/block}
