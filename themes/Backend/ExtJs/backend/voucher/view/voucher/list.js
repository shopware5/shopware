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
 * @package    Voucher
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/voucher/view/voucher}

/**
 * Shopware UI - Voucher list main window.
 *
 * Displays all List Voucher Information
 */
/**
 * Default voucher list view. Extends a grid view.
 */
//{block name="backend/voucher/view/voucher/list"}
Ext.define('Shopware.apps.Voucher.view.voucher.List', {
    extend:'Ext.grid.Panel',
    border: false,
    alias:'widget.voucher-voucher-list',
    region:'center',
    autoScroll:true,
    store:'List',
    ui:'shopware-ui',
     //to select text like the voucher code
    selType:'cellmodel',
    /*{if {acl_is_allowed privilege=update}}*/
    plugins:[
        Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit:1
        })
    ],
    /*{/if}*/
    /**
     * Initialize the Shopware.apps.Voucher.view.main.List and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.registerEvents();

        /*{if {acl_is_allowed privilege=create || acl_is_allowed privilege=update}}*/
        me.selModel = me.getGridSelModel();
        /*{/if}*/

        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();
        me.store = me.listStore;
        me.dockedItems = [ me.toolbar, me.pagingbar ];
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
                 * @event deleteColumn
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'deleteColumn',

                /**
                 * Event will be fired when the user clicks the delete icon in the
                 * action column
                 *
                 * @event deleteColumn
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'editColumn',

                /**
                 * Event will be fired when the user clicks the duplicate icon in the
                 * action column
                 *
                 * @event duplicateColumn
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'duplicateColumn'
        );

        return true;
    },
    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;

        var columnsData = [
            {
                header:'{s name=list/column/description}Description{/s}',
                dataIndex:'description',
                flex:1,
                renderer:this.nameRenderer
            },
            {
                header:'{s name=list/column/code}Code{/s}',
                dataIndex:'voucherCode',
                flex:1,
                editor:{
                    xtype:'textfield',
                    allowBlank:false,
                    readOnly:true
                },
                renderer:this.codeRenderer
            },
            {
                header:'{s name=list/column/mode}Voucher mode{/s}',
                dataIndex:'modus',
                renderer:this.modeRenderer,
                flex:1
            },
            {
                header:'{s name=list/column/redeemed}Redeemed total{/s}',
                dataIndex:'checkedIn',
                flex:1,
                renderer:this.checkedInRenderer
            },
            {
                header:'{s name=list/column/value}Value{/s}',
                dataIndex:'value',
                renderer:this.valueRenderer,
                flex:1
            },
            {
                xtype:'datecolumn',
                header:'{s name=list/column/valid_from}Valid from{/s}',
                dataIndex:'validFrom',
                flex:1
            },
            {
                xtype:'datecolumn',
                header:'{s name=list/column/valid_to}Valid till{/s}',
                dataIndex:'validTo',
                flex:1
            },
            {
                xtype:'actioncolumn',
                width:90,
                items:me.getActionColumnItems()
            }
        ];
        return columnsData;
    },
    /**
     * Creates the items of the action column
     *
     * @return [array] action column itesm
     */
    getActionColumnItems: function () {
        var me = this,
            actionColumnData = [];

            /*{if {acl_is_allowed privilege=update}}*/
            actionColumnData.push({
                iconCls:'sprite-pencil',
                cls:'editBtn',
                tooltip:'{s name=list/action_column/edit}Edit this voucher{/s}',
                handler:function (view, rowIndex, colIndex, item) {
                    me.fireEvent('editColumn', view, rowIndex, colIndex, item);
                }
            });
            /*{/if}*/

            /*{if {acl_is_allowed privilege=delete}}*/
            actionColumnData.push({
               iconCls:'sprite-minus-circle-frame',
               action:'delete',
               cls:'delete',
               tooltip:'{s name=list/action_column/delete}Delete this voucher{/s}',
               handler:function (view, rowIndex, colIndex, item) {
                   me.fireEvent('deleteColumn', view, rowIndex, colIndex, item);
               }
            });
            /*{/if}*/

            /*{if {acl_is_allowed privilege=create}}*/
            actionColumnData.push({
                iconCls:'sprite-blue-document-copy',
                cls:'duplicate',
                tooltip:'{s name=list/action_column/duplicate}Duplicate this voucher{/s}',
                handler:function (view, rowIndex, colIndex, item) {
                    me.fireEvent('duplicateColumn', view, rowIndex, colIndex, item);
                }

            });
            /*{/if}*/
        return actionColumnData;
    },
    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        return Ext.create('Ext.toolbar.Toolbar',
            {
                dock:'top',
                ui:'shopware-ui',
                items:[
                    /*{if {acl_is_allowed privilege=create}}*/
                    {
                        iconCls:'sprite-plus-circle',
                        text:'{s name=list/button/add}Add{/s}',
                        action:'add'
                    },
                    /*{/if}*/
                    /*{if {acl_is_allowed privilege=delete}}*/
                    {

                        iconCls:'sprite-minus-circle-frame',
                        text:'{s name=list/button/delete}Delete selected voucher{/s}',
                        disabled:true,
                        action:'deleteVoucher'

                    },
                    /*{/if}*/
                    '->',
                    {
                        xtype:'textfield',
                        name:'searchfield',
                        action:'searchVoucher',
                        width:170,
                        cls: 'searchfield',
                        enableKeyEvents:true,
                        checkChangeBuffer: 500,
                        emptyText:'{s name=list/field/search}Search...{/s}'
                    },
                    { xtype:'tbspacer', width:6 }
                ]
            });
    },
    /**
     * Creates the paging toolbar for the voucher grid to allow
     * and store paging. The paging toolbar uses the same store as the Grid
     *
     * @return Ext.toolbar.Paging The paging toolbar for the customer grid
     */
    getPagingBar: function () {
        var me = this;
        return Ext.create('Ext.toolbar.Paging', {
            store:me.listStore,
            dock:'bottom',
            displayInfo:true
        });

    },
    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel:function () {
        var selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    var owner = this.view.ownerCt,
                    btn = owner.down('button[action=deleteVoucher]');
                    btn.setDisabled(selections.length == 0);
                }
            }
        });
        return selModel;
    },

    //////////////////////////////////////////////////////////////////////////
    //Render methods /////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////
    /**
     * Mode Renderer Method
     * @param value
     */
    modeRenderer:function (value) {
        if(value!=1){
            return "{s name=list/render_value/mode/general}General{/s}";
        }
        return "{s name=list/render_value/mode/individual}Individual{/s}";
    },
    /**
     * Value Renderer Method
     * @param value
     */
    valueRenderer:function (value,p,r) {
        if(r.data.percental == 1){
            return value.replace(/[.,]/, Ext.util.Format.decimalSeparator)+" %";
        }
        return value.replace(/[.,]/, Ext.util.Format.decimalSeparator);
    },
    /**
     * Name Renderer Method
     * @param value
     */
    nameRenderer:function (value) {
        return Ext.String.format('{literal}<strong style="font-weight: 700">{0}</strong>{/literal}', value);
    },
    /**
     * Code Renderer Method
     * @param value
     */
    codeRenderer:function (value) {
        return Ext.String.format('{literal}<strong style="font-weight: 700">{0}</strong>{/literal}', value);
    },
    /**
     * Checked in Renderer Method to show all cashed Vouchers
     * @param value
     */
    checkedInRenderer:function (value, p, r) {
        var numberOfUnits = r.data.numberOfUnits;
        if (value < numberOfUnits) {
            return '<span style="color:green;">' + value + ' / '  + numberOfUnits +'</span>';
        }
        else {
            return '<span style="color:red;">' + value + ' / '  + numberOfUnits + '</span>';
        }
    }
});
//{/block}
