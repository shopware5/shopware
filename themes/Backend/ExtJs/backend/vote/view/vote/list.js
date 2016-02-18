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
 * @package    Vote
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/vote/main}

/**
 * Shopware UI - Vote view vote-grid
 *
 * This grid contains all votes and the actioncolumn.
 * In the actioncolumn are three buttons: Answer, delete, accept.
 */
//{block name="backend/vote/view/vote/list"}
Ext.define('Shopware.apps.Vote.view.vote.List', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.grid.Panel',

    ui: 'shopware-ui',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('vote-main-list')
    * @string
    */
    alias: 'widget.vote-main-list',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'center',
    /**
    * The view needs to be scrollable
    * @string
    */
    autoScroll: true,


    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.store = me.voteStore;
        me.selModel = me.getGridSelModel();
        me.columns = me.getColumns();

        // Add paging toolbar to the bottom of the grid panel
        me.dockedItems = [{
            dock: 'bottom',
            region: 'south',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        }];

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
             * Event will be fired when the user clicks the comment icon in the
             * action column
             *
             * @event commentColumn
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'commentColumn',

            /**
             * Event will be fired when the user clicks the add icon in the
             * action column
             *
             * @event addColumn
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'addColumn'
        );

        return true;
    },

    /**
     * Creates the checkbox model and the listeners for it
     */
    getGridSelModel: function(){
        var selModel = Ext.create('Ext.selection.CheckboxModel',{
            listeners: {
                selectionchange: function(sm, selections){
                    var owner = this.view.ownerCt.ownerCt;
                    var btnAccept = owner.down('button[action=acceptMultipleVotes]');
                    var btnDelete = owner.down('button[action=deleteMultipleVotes]');

                    // If an article is marked
                    btnDelete.setDisabled(selections.length == 0);
                    btnAccept.setDisabled(selections.length == 0);
                }
            }
        });
        return selModel;
    },

    /**
     * Creates the columns
     */
    getColumns: function(){
        var me = this;

        return [
            {
                header: '{s name=column/status}Status{/s}',
                dataIndex: 'active',
                width: 58,
                renderer: me.statusColumn
            },{
                header: '{s name=column/date}Datum{/s}',
                dataIndex: 'datum',
                xtype: 'datecolumn',
                flex: 1
            },{
                header: '{s name=column/article}Article{/s}',
                dataIndex: 'articleName',
                flex: 1
            },{
                header: '{s name=column/author}Author{/s}',
                dataIndex: 'name',
                flex: 1
            },{
                header: '{s name=column/headline}Headline{/s}',
                dataIndex: 'headline',
                flex: 1
            },{
                header: '{s name=column/points}Points{/s}',
                dataIndex: 'points',
                flex: 1,
                renderer: me.pointsColumn
            },
            {
                xtype: 'actioncolumn',
                width: 85,
                items: [
                    /*{if {acl_is_allowed privilege=accept}}*/
                    {
                        action: 'addColumn',
                        tooltip: '{s name=column/actioncolumn/add}Accept vote{/s}',
                        getClass: function (v, meta, rec) {
                            return rec.get('active') ? 'x-hidden' : 'sprite-plus-circle'
                        },
                        handler: function (view, rowIndex) {
                            me.fireEvent('addColumn', rowIndex);
                        }
                    },
                    /*{/if}*/
                    /*{if {acl_is_allowed privilege=delete}}*/
                    {
                        iconCls: 'sprite-minus-circle',
                        action: 'deleteColumn',
                        tooltip: '{s name=column/actioncolumn/delete}Delete vote{/s}',
                        handler: function (view, rowIndex) {
                            me.fireEvent('deleteColumn', rowIndex);
                        }
                    },
                    /*{/if}*/
                    /*{if {acl_is_allowed privilege=comment}}*/
                    {
                        iconCls: 'sprite-balloon--pencil',
                        action: 'commentColumn',
                        tooltip: '{s name=column/actioncolumn/comment}Comment on vote{/s}',
                        handler: function (view, rowIndex) {
                            me.fireEvent('commentColumn', rowIndex);
                        }
                    }
                    /*{/if}*/
                ]
            }
        ];
    },

    /**
     * Function to render the active-column as a tick-icon or a cross-icon
     * @param value Contains the active-value
     */
    statusColumn: function(value){
        if(value==1){
            return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-tick-small"></div>')
        }else{
            return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-cross-small"></div>')
        }
    },

    /**
     * Function to render the points column
     * @param value Contains the active-value
     */
    pointsColumn: function(value){
        return value+'/5';
    }
});
//{/block}
