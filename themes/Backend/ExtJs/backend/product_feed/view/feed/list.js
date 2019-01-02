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
 * @package    ProductFeed
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/product_feed/view/feed}

/**
 * Shopware UI - Product Feed list main window.
 *
 * Default feed list view. Extends a grid view.
 */
//{block name="backend/product_feed/view/feed/list"}
Ext.define('Shopware.apps.ProductFeed.view.feed.List', {
    extend:'Ext.grid.Panel',
    border: false,
    alias:'widget.product_feed-feed-list',
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
                'duplicateColumn',

                /**
                 * Event will be fired when the user clicks the exectue icon in the
                 * action column
                 *
                 * @event executeFeed
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'executeFeed'
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
                header:'{s name=list/column/title}Title{/s}',
                dataIndex:'name',
                flex:1
            },
            {
                header: '{s name=list/column/active}Active{/s}',
                dataIndex: 'active',
                width: 50,
                renderer:me.activeColumnRenderer
            },
            {
                header:'{s name=list/column/file_name}File name{/s}',
                dataIndex:'fileName',
                renderer:me.fileNameRenderer,
                flex:1
            },
            {
                header:'{s name=list/column/count_articles}Number of articles{/s}',
                dataIndex:'countArticles',
                flex:1
            },
            {
                header:'{s name=list/column/last_export}Last export{/s}',
                dataIndex:'lastExport',
                flex:1,
                renderer :  me.onDateRenderer
            },
            {
                xtype:'actioncolumn',
                width:110,
                items:me.getActionColumnItems()
            }
        ];
        return columnsData;
    },

    onDateRenderer : function(value) {
        if(!value) {
            return;
        }
        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
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
                tooltip:'{s name=list/action_column/edit}Edit this product feed{/s}',
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
               tooltip:'{s name=list/action_column/delete}Delete this feed{/s}',
               handler:function (view, rowIndex, colIndex, item) {
                   me.fireEvent('deleteColumn', view, rowIndex, colIndex, item);
               }
            });
            /*{/if}*/

            /*{if {acl_is_allowed privilege=create}}*/
            actionColumnData.push({
                iconCls:'sprite-blue-document-copy',
                cls:'duplicate',
                tooltip:'{s name=list/action_column/duplicate}Duplicate this feed{/s}',
                handler:function (view, rowIndex, colIndex, item) {
                    me.fireEvent('duplicateColumn', view, rowIndex, colIndex, item);
                }

            });
            /*{/if}*/

            /*{if {acl_is_allowed privilege=generate}}*/
            actionColumnData.push({
                iconCls:'sprite-lightning',
                cls:'arrow-lightning',
                tooltip:'{s name=list/action_column/execute}Execute feed{/s}',
                handler:function (view, rowIndex, colIndex, item) {
                    me.fireEvent('executeFeed', view, rowIndex, colIndex, item);
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
     * Creates the paging toolbar for the product feed grid to allow
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
     * Formats the Filename Column and adds a Link to the Feed
     *
     * @param [string] - The order time value
     * @return [string] - The passed value
     */
    fileNameRenderer:function (value, p, record) {
        /*{if {acl_is_allowed privilege=generate}}*/
        return '<a href="{url controller=export}' + '/index/'+record.get('fileName')+
                '?feedID='+record.get('id')+'&hash='+ record.get('hash') + '" target="_blank">' + value + '</a>';
        /*{else}*/
        return value;
        /*{/if}*/
    },

     /**
      * @param [object] - value
      */
     activeColumnRenderer: function(value) {
         var cls = 'sprite-ui-check-box';
         if (!value) {
            cls = 'sprite-cross-small';
         }
         return '<div class="'+cls+'" style="width: 16px; height: 16px; margin-left: 9px;">&nbsp;</div>';
     }
});
//{/block}
