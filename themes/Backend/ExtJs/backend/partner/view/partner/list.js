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
 * @package    Partner
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/partner/view/partner}

/**
 * Shopware UI - partner list window.
 *
 * Displays the partner list
 */
//{block name="backend/partner/view/partner/list"}
Ext.define('Shopware.apps.Partner.view.partner.List', {
    extend:'Ext.grid.Panel',
    border: false,
    alias:'widget.partner-partner-list',
    region:'center',
    autoScroll:true,
    store:'List',
    ui:'shopware-ui',
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
                 * @event editColumn
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'editColumn',

                /**
                 * Event will be fired when the user clicks the exectue icon in the
                 * action column
                 *
                 * @event statistic
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'statistic'
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
                header:'{s name=list/column/company}Company{/s}',
                dataIndex:'company',
                flex:1
            },
            {
                header:'{s name=list/column/registered}Registered{/s}',
                dataIndex:'date',
                xtype: 'datecolumn',
                flex:1
            },
            {
                header:'{s name=list/column/active}Active{/s}',
                dataIndex:'active',
                renderer: me.activeRenderer,
                flex:1
            },
            {
                header:'{s name=list/column/monthly_amount}Monthly turnover{/s}',
                dataIndex:'monthlyAmount',
                xtype: 'numbercolumn',
                flex:1
            },
            {
                header:'{s name=list/column/yearly_amount}Yearly turnover{/s}',
                dataIndex:'yearlyAmount',
                xtype: 'numbercolumn',
                flex:1
            },
            {
                header:'{s name=list/column/partner_link}Partner link{/s}',
                dataIndex:'idCode',
                renderer: me.partnerLinkRenderer,
                flex:1
            },
            {
                xtype:'actioncolumn',
                width:130,
                items:me.getActionColumnItems()
            }
        ];
        return columnsData;
    },

    /**
     * renders the active field of the grid
     *
     * @param value
     * @return { String }
     */
    activeRenderer : function(value) {
        if(value) {
            return "<span style='font-weight: 700; color:green;'>{s name=list/active_value/yes}Yes{/s}</span>";
        }
        return "<span style='font-weight: 700; color:red;'>{s name=list/active_value/no}No{/s}</span>";
    },

    /**
     * renders the partner link of the grid
     *
     * @param value
     * @return { String }
     */
    partnerLinkRenderer : function(value) {
        return '<a href="{url controller=partner action=redirectToPartnerLink}' + '?sPartner='+ value + '" target="_blank">' + 'link' + '</a>';
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
                tooltip:'{s name=list/action_column/edit}Edit partner{/s}',
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
               tooltip:'{s name=list/action_column/delete}Delete partner{/s}',
               handler:function (view, rowIndex, colIndex, item) {
                   me.fireEvent('deleteColumn', view, rowIndex, colIndex, item);
               }
            });
            /*{/if}*/

            /*{if {acl_is_allowed privilege=statistic}}*/
            actionColumnData.push({
                iconCls:'sprite-partner-stats',
                cls:'chart-up-color',
                tooltip:'{s name=list/action_column/statistic}Statistics{/s}',
                handler:function (view, rowIndex, colIndex, item) {
                    me.fireEvent('statistic', view, rowIndex, colIndex, item);
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
                    }
                    /*{/if}*/
                ]
            });
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
            store:me.listStore,
            dock:'bottom',
            displayInfo:true
        });

    }
});
//{/block}
