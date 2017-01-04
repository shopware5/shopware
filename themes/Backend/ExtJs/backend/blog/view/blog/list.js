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
 * @package    Blog
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/blog/view/blog}

/**
 * Shopware UI - Blog list main window.
 *
 * Displays all Blog Information
 */
/**
 * Default blog list view. Extends a grid panel
 */
//{block name="backend/blog/view/blog/list"}
Ext.define('Shopware.apps.Blog.view.blog.List', {
    extend:'Ext.grid.Panel',
    border: false,
    alias:'widget.blog-blog-list',
    region:'center',
    autoScroll:true,
    store:'List',
    ui:'shopware-ui',
    selType:'cellmodel',
    /**
     * Initialize the Shopware.apps.Blog.view.blog.List and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.registerEvents();

        me.selModel = me.getGridSelModel();

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
                'deleteBlogArticle',

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
                'editBlogArticle',

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
                header:'{s name=list/column/title}Title{/s}',
                dataIndex:'title',
                renderer: me.titleRenderer,
                flex:6
            },
            {
                header:'{s name=list/column/number_of_comments}Pending comments{/s}',
                dataIndex:'numberOfComments',
                renderer: me.greenRenderer,
                flex:3
            },
            {
                header:'{s name=list/column/views}Views{/s}',
                dataIndex:'views',
                renderer: me.greenRenderer,
                flex:1
            },
            {
                header:'{s name=list/column/date}Display at{/s}',
                dataIndex:'displayDate',
                renderer: me.dateRenderer,
                flex:3
            },
            {
                header:'{s name=list/column/active}Active{/s}',
                dataIndex:'active',
                renderer: me.activeColumnRenderer,
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
     * @return [array] action column items
     */
    getActionColumnItems: function () {
        var me = this,
            actionColumnData = [];


        actionColumnData.push({
            iconCls:'sprite-pencil',
            cls:'editBtn',
            tooltip:'{s name=list/action_column/edit}Edit this blog article{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('editBlogArticle', view, rowIndex, colIndex, item);
            }
        });

        actionColumnData.push({
            iconCls:'sprite-minus-circle-frame',
            action:'delete',
            cls:'delete',
            tooltip:'{s name=list/action_column/delete}Delete this blog article{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('deleteBlogArticle', view, rowIndex, colIndex, item);
            }
        });

        actionColumnData.push({
            iconCls:'sprite-blue-document-copy',
            cls:'duplicate',
            tooltip:'{s name=list/action_column/duplicate}Duplicate this blog{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('duplicateColumn', view, rowIndex, colIndex, item);
            }

        });

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
            /* {if {acl_is_allowed privilege=create}} */
                    {
                        iconCls:'sprite-plus-circle',
                        text:'{s name=list/button/add}Add blog article{/s}',
                        action:'add'
                    },
            /* {/if} */
            /* {if {acl_is_allowed privilege=delete}} */
                    {

                        iconCls:'sprite-minus-circle-frame',
                        text:'{s name=list/button/delete}Delete selected blog articles{/s}',
                        disabled:true,
                        action:'deleteBlogArticles'

                    },
            /* {/if} */
                    '->',
                    {
                        xtype:'textfield',
                        name:'searchfield',
                        action:'searchBlogArticles',
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
     * Creates the paging toolbar for the blog grid to allow
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
                // Unlocks the delete button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    var owner = this.view.ownerCt,
                    btn = owner.down('button[action=deleteBlogArticles]');
                    btn.setDisabled(!selections.length);
                }
            }
        });
        return selModel;
    },

    /**
     * Renderer for the views column
     *
     * @param [object] - value
     */
    greenRenderer: function(value) {
        return '<span style="color:green; font-weight: 700;">' + value + '</span>';
    },

    /**
     * title Renderer Method
     *
     * @param value
     */
    titleRenderer:function (value) {
        return Ext.String.format('{literal}<strong style="font-weight: 700">{0}</strong>{/literal}', value);
    },

    /**
     * Renderer function of the DisplayDate column
     *
     * @param value
     * @param metaData
     * @param record
     */
    dateRenderer: function(value, metaData, record) {
        if (record.get('displayDate') === Ext.undefined) {
            return record.get('displayDate');
        }
        return Ext.util.Format.date(record.get('displayDate')) + ' ' + Ext.util.Format.date(record.get('displayDate'), timeFormat);
    },

    /**
     * Renderer for the active flag
     *
     * @param [object] - value
     */
    activeColumnRenderer: function(value) {
        if (value) {
            return '<div class="sprite-tick"  style="width: 25px; height: 25px">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross" style="width: 25px; height: 25px">&nbsp;</div>';
        }
    }
});
//{/block}
