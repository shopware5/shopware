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
//{block name="backend/form/view/main/list"}
Ext.define('Shopware.apps.Form.view.main.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.form-main-formgrid',
    region: 'center',
    autoScroll: true,

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.store = me.formStore;

        me.selModel    = me.getGridSelModel();
        me.columns     = me.getColumns();
        me.dockedItems = [ me.getToolbar(), me.getPagingbar() ];

        me.callParent(arguments);
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel: function () {
        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                selectionchange: function (sm, selections) {
                    var owner = this.view.ownerCt,
                        btn = owner.down('button[action=delete]');

                    /*{if {acl_is_allowed privilege=delete}}*/
                    btn.setDisabled(selections.length === 0);
                    /*{/if}*/
                }
            }
        });
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var actionColumItems = [];

        /*{if {acl_is_allowed privilege=delete}}*/
        actionColumItems.push({
            action: 'delete',
            cls: 'deleteBtn',
            tooltip: '{s name=tooltip_delete_form}Delete this form{/s}',
            iconCls: 'sprite-minus-circle-frame'
        });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=createupdate}}*/
        actionColumItems.push({
            action: 'edit',
            cls: 'editBtn',
            iconCls: 'sprite-pencil',
            tooltip: '{s name=tooltip_edit_form}Edit this form{/s}'
        });
        /*{else}*/
        actionColumItems.push({
            action: 'edit',
            cls: 'editBtn',
            iconCls: 'sprite-magnifier',
            tooltip: '{s name=tooltip_show_form}Display form{/s}'
        });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=createupdate}}*/
        actionColumItems.push({
            iconCls: 'sprite-blue-document-copy',
            action: 'copy',
            cls: 'copyBtn',
            tooltip: '{s name=tooltip_copy_form}Copy this form{/s}'
        });
        /*{/if}*/

        return [{
            header: '{s name=column_name}Name{/s}',
            dataIndex: 'name',
            flex: 1
        }, {
            header: '{s name=column_email}Email address{/s}',
            dataIndex: 'email',
            flex: 1
        }, {
            header: '{s name=column_active}Active{/s}',
            dataIndex: 'active',
            renderer: this.activeColumnRenderer,
            width: 40
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: actionColumItems.length * 26,
            items: actionColumItems
        }];
    },

    /**
     * Renderer for the active flag
     *
     * @param [object] - value
     */
    activeColumnRenderer: function(value) {
        if (value) {
            return '<div class="sprite-ui-check-box"  style="display:block; margin: 0 auto; width: 14px; height: 14px">&nbsp;</div>';
        } else {
            return '<div class="sprite-ui-check-box-uncheck" style="display:block; margin: 0 auto; width: 14px; height: 14px">&nbsp;</div>';
        }
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui : 'shopware-ui',
            items: [

            /*{if {acl_is_allowed privilege=createupdate}}*/
            {
                iconCls: 'sprite-plus-circle-frame',
                text: '{s name=toolbar_add_form}Add{/s}',
                action: 'add'
            },
            /*{/if}*/

            /*{if {acl_is_allowed privilege=delete}}*/
            {
                iconCls:'sprite-minus-circle-frame',
                text: '{s name=toolbar_delete}Delete all selected{/s}',
                disabled: true,
                action:'delete'
            },
            /*{/if}*/

            '->', {
                xtype : 'textfield',
                name : 'searchfield',
                action : 'searchForms',
                width: 170,
                cls: 'searchfield',
                enableKeyEvents: true,
                checkChangeBuffer: 500,
                emptyText : '{s name=toolbar_search}Search...{/s}'
            }, {
                xtype: 'tbspacer',
                width: 6
            }]
        });
    },

    /**
     * Creates pagingbar shown at the bottom of the grid
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function () {
        return Ext.create('Ext.toolbar.Paging', {
            store: this.store,
            dock: 'bottom',
            displayInfo: true
        });
    }
});
//{/block}
