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
 * @package    Form
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/form/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/form/view/main/fieldgrid"}
Ext.define('Shopware.apps.Form.view.main.Fieldgrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.form-main-fieldgrid',
    title : '{s name=title_fields}Fields{/s}',
    autoScroll: true,
    selType: 'rowmodel',
    sortableColumns: false,

    /**
     * Contains snippets for this view
     * @object
     */
    messages: {
         tooltipValue: '{s name=tooltip_value}For selections, checkboxes or radios use a semicolon to separate the values{/s}',
         tooltipName: '{s name=tooltip_name}To enter two inputs, use a semicolon to separate the names{/s}',
         tooltipSmartyEnabled: '{s name=tooltip_smarty}Smarty code is allowed{/s}',
         tooltipDragDrop: '{s name=tooltip_dragdrop}You can move rows via Drag & Drop{/s}',
         hintDragDrop: '{s name=hint_dragdrop}You can move rows via Drag & Drop{/s}',
         saveBtnText: '{s name=rowedit_save}Save{/s}',
         cancelBtnText: '{s name=rowedit_cancel}Cancel{/s}'
    },

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.store       = me.fieldStore;

        me.columns     = me.getColumns();
        me.viewConfig  = me.getViewConfig();
        me.editor      = me.getRowEditingPlugin();
        me.plugins     = [ me.editor, me.getHeaderToolTipPlugin() ];
        me.dockedItems = [ me.getToolbar(),  me.getPagingbar() ];

        me.callParent(arguments);
    },

    /**
     * Creates headertooltip plugin
     *
     * @return [Shopware.grid.HeaderToolTip]
     */
    getHeaderToolTipPlugin: function() {
        var headerToolTipPlugin = Ext.create('Shopware.grid.HeaderToolTip', {
            showIcons: true
        });

        return headerToolTipPlugin;
    },

    /**
     * Creates row editing plugin
     *
     * @return [Ext.grid.plugin.RowEditing]
     */
    getRowEditingPlugin: function() {
        var me = this, rowEditingPlugin = Ext.create('Ext.grid.plugin.RowEditing', {
            saveBtnText : me.messages.saveBtnText,
            cancelBtnText : me.messages.cancelBtnText,
            errorSummary: false
        });

        return rowEditingPlugin;
    },

    /**
     * Creates gridviewdragdrop plugin
     *
     * @return [object]
     */
    getViewConfig: function() {
        var viewConfig = {
            /*{if {acl_is_allowed privilege=createupdate}}*/
            plugins: {
                pluginId: 'my-gridviewdragdrop',
                ptype: 'gridviewdragdrop'
            }
            /*{/if}*/
        };

        return viewConfig;
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        var columns = [
        /*{if {acl_is_allowed privilege=createupdate}}*/
        {
            header: '&#009868;',
            width: 24,
            hideable: false,
            renderer : me.renderSorthandleColumn,
        },
        /*{/if}*/
        {
            header: '{s name=column_name}Name{/s}',
            dataIndex: 'name',
            tooltip: me.messages.tooltipName,
            flex: 1,
            hideable: false,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }, {
            header: '{s name=column_label}Label{/s}',
            dataIndex: 'label',
            flex: 1,
            hideable: false,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }, {
            header: '{s name=column_typ}Typ{/s}',
            dataIndex: 'typ',
            flex: 1,
            hideable: false,
            editor: {
                xtype: 'combo',
                allowBlank: false,
                editable: false,
                mode: 'local',
                triggerAction: 'all',
                displayField: 'label',
                valueField: 'id',
                store: me.getTypComboStore()
            }
        }, {
            header: '{s name=column_class}Class{/s}',
            dataIndex: 'class',
            flex: 1,
            hideable: false,
            editor: {
                xtype: 'combo',
                allowBlank: false,
                editable: false,
                mode: 'local',
                triggerAction: 'all',
                displayField: 'label',
                valueField: 'id',
                store: me.getClassComboStore()
            }
        }, {
            header: '{s name=column_value}Value{/s}',
            dataIndex: 'value',
            tooltip: me.messages.tooltipValue,
            flex: 1,
            hideable: false,
            editor: {
                xtype:'textfield'
            }
        }, {
            header: '{s name=column_note}Note{/s}',
            dataIndex: 'note',
            tooltip: me.messages.tooltipSmartyEnabled,
            flex: 1,
            hideable: false,
            editor: {
                xtype:'textfield'
            }
        }, {
            header: '{s name=column_errormsg}Error Message{/s}',
            tooltip: me.messages.tooltipSmartyEnabled,
            dataIndex: 'errorMsg',
            flex: 1,
            hideable: false,
            editor: {
                xtype:'textfield'
            }
        }, {
            xtype: 'booleancolumn',
            header: '{s name=column_required}Required{/s}',
            dataIndex: 'required',
            flex: 1,
            hideable: false,
            editor: {
                xtype: 'checkbox',
                inputValue: true,
                uncheckedValue: false
            }
        }

        /*{if {acl_is_allowed privilege=createupdate}}*/
        ,{
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: 24,
            hideable: false,
            items: [{
                iconCls: 'sprite-minus-circle-frame',
                action: 'delete',
                cls: 'delete',
                tooltip: '{s name=tooltip_delete_field}Delete this field{/s}'
            }]
        }
        /*{/if}*/
        ];

        return columns;
    },



    /**
     * Creates store object used for the typ column
     *
     * @return [Ext.data.SimpleStore]
     */
    getTypComboStore: function() {
        return new Ext.data.SimpleStore({
            fields:['id', 'label'],
            data: [
                ['text', 'Text'],
                ['text2', 'Text2'],
                ['checkbox', 'Checkbox'],
                ['email', 'Email'],
                ['select', 'select'],
                ['textarea', 'textarea']
            ]
        });
    },

    /**
     * Creates store object used for the class column
     *
     * @return [Ext.data.SimpleStore]
     */
    getClassComboStore: function() {
        return new Ext.data.SimpleStore({
            fields:['id', 'label'],
            data: [
                ['normal', 'normal'],
                ['strasse;nr', 'strasse;nr'],
                ['plz;ort', 'plz;ort']
            ]
        });
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: [

            /*{if {acl_is_allowed privilege=createupdate}}*/
            {
                iconCls: 'sprite-plus-circle-frame',
                text: '{s name=toolbar_add_field}Add Field{/s}',
                action: 'add'
            },
            /*{/if}*/

            /*{if {acl_is_allowed privilege=createupdate}}*/
            {
                xtype: 'tbfill'
            }, {
                xtype: 'container',
                html: '<p style="padding: 5px">' + me.messages.hintDragDrop + '</p>'
            }
            /*{/if}*/
            ]
        });

        return toolbar;
    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function () {
        var pagingbar =  Ext.create('Ext.toolbar.Paging', {
            store: this.store,
            dock:'bottom',
            displayInfo: true
        });

        return pagingbar;
    },

    /**
     * Renderer for sorthandle-column
     *
     * @param [string] value
     */
    renderSorthandleColumn: function (value,  metadata) {
        var me = this;

        metadata.tdAttr = 'data-qtip="' + me.messages.hintDragDrop +'"';

        return '<div style="cursor: n-resize;">&#009868;</div>';
    }
});
//{/block}
