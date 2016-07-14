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

//{namespace name=backend/voucher/view/code}

/**
 * Shopware UI - Voucher Code List page additional panel
 *
 * Displayed on the right code panel.
 */
//{block name="backend/voucher/view/code/list"}
Ext.define('Shopware.apps.Voucher.view.code.List', {
    extend:'Ext.grid.Panel',
    title:'{s name=detail_codes/win_title/code}Individual voucher codes{/s}',
    border:false,
    alias:'widget.voucher-code-list',
    region:'center',
    autoScroll:true,
    store:'Codes',
    ui:'shopware-ui',
    //to select text like the voucher code
    selType:'cellmodel',
    plugins:[
        Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit:1
        })
    ],
    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.registerEvents();
        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();
        me.store = me.codeStore;
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
                 * @event customerLink
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'openCustomerAccount'
        );

        return true;
    },
    /**
     * Sets up the ui component
     *
     * @return Array
     */
    getColumns:function () {
        var me = this;

        // Define the columns and renderers
        return [
            {
                header:'{s name=detail_codes/column/code}Code{/s}',
                dataIndex:'code',
                flex:1,
                editor:{
                    xtype:'textfield',
                    allowBlank:false,
                    readOnly:true
                }
            },
            {
                header:'{s name=detail_codes/column/cashed}Redeemed{/s}',
                dataIndex:'cashed',
                flex:1,
                renderer:me.cashedRenderer,
                editor: me.createRedeemedEditor()
            },
            {
                header:'{s name=detail_codes/column/customer_number}Customer number{/s}',
                dataIndex:'number',
                flex:1,
                editor:{
                    xtype:'textfield',
                    allowBlank:false,
                    readOnly:true
                }
            },
            {
                header:'{s name=detail_codes/column/first_name}First name{/s}',
                dataIndex:'firstName',
                flex:1
            },
            {
                header:'{s name=detail_codes/column/last_name}Last name{/s}',
                dataIndex:'lastName',
                flex:1
            },
            {
                xtype:'actioncolumn',
                width:60,
                align:'center',
                items:[{
                    iconCls:'x-action-col-icon sprite-user--pencil',
                    cls:'sprite-user--pencil',
                    tooltip:'{s name=list/action_column/link_customer}To customer account{/s}',
                    getClass: function(value, metadata, record) {
                        if (!record.get("customerId")) {
                            return 'x-hidden';
                        }
                    },
                    handler:function (view, rowIndex, colIndex, item) {
                        me.fireEvent('openCustomerAccount', view, rowIndex, colIndex, item);
                    }
                }]
            }
        ];
    },

    /**
     * Creates and returns a new checkbox to edit the codes `cashed` property.
     *
     * @returns [Ext.form.field.Checkbox]
     */
    createRedeemedEditor: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            store: new Ext.data.SimpleStore({
                fields:[
                    'id',
                    'name'
                ],
                data: [
                    [false, '{s name=list/render_value/cashed/no}No{/s}'],
                    [true, '{s name=list/render_value/cashed/yes}Yes{/s}']
                ]
            }),
            displayField: 'name',
            valueField: 'id'
        });
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            id:'voucherCodeToolbar',
            alias:'voucherCodeToolbar',
            ui:'shopware-ui',
            items:[

                {
                    /*{if {acl_is_allowed privilege=generate}}*/
                    iconCls:'sprite-plus-circle',
                    text:'{s name=detail_codes/button/create_new_codes}Create new codes{/s}',
                    action:'generateCodes'
                    /*{/if}*/
                },
                {
                    /*{if {acl_is_allowed privilege=generate}}*/
                    xtype:'textfield',
                    name:'patternField',
                    width:200,
                    helpText:'{s name=detail_codes/helptext/voucher_code_pattern}Optional you can enter your desired voucher code format. The Pattern %s generates a word character and the pattern %d a number. Make sure your code pattern is complex enough.{/s}',
                    stripCharsRe:/\\/,
                    emptyText:'{s name=detail_codes/emptytext/voucher_code_pattern}Voucher code pattern...{/s}'
                    /*{/if}*/
                },
                '-',
                {
                    /*{if {acl_is_allowed privilege=export}}*/
                    iconCls:'sprite-drive-download',
                    text:'{s name=detail_codes/button/download_codes}Download codes{/s}',
                    action:'downloadCodes',
                    disabled: true
                    /*{/if}*/
                },
                '->',
                {
                    xtype:'textfield',
                    name:'searchfield',
                    action:'searchVoucherCode',
                    width:170,
                    cls:'searchfield',
                    enableKeyEvents:true,
                    checkChangeBuffer:500,
                    emptyText:'{s name=detail_codes/field/search}Search...{/s}'
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
    getPagingBar:function () {
        var me = this;
        return Ext.create('Ext.toolbar.Paging', {
            store:me.codeStore,
            dock:'bottom',
            displayInfo:true
        });

    },
    /**
     * Renderer to show the right value of the cashed field
     *
     * @param value
     */
    cashedRenderer:function (value) {
        if(value==1){
             return "{s name=list/render_value/cashed/yes}Yes{/s}";
         }
         return "{s name=list/render_value/cashed/no}No{/s}";
     }
});
//{/block}
