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
 * Shopware UI - Blog comments grid.
 *
 * Displays all Blog Comment Information
 */
/**
 * Default blog comment list view. Extends a grid panel.
 */
//{block name="backend/blog/view/blog/detail/comments/grid"}
Ext.define('Shopware.apps.Blog.view.blog.detail.comments.Grid', {
    extend:'Ext.grid.Panel',
    border: false,
    alias:'widget.blog-blog-detail-comments-grid',
    region:'center',
    autoScroll:true,
    store:'List',
    ui:'shopware-ui',
    split: true,
    selType:'cellmodel',

    /**
     * Initialize the Shopware.apps.Blog.view.blog.detail.comments and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;
        me.selModel = me.getGridSelModel();

        me.shopStore = Ext.create('Shopware.apps.Base.store.Shop');
        me.shopStore.clearFilter();
        me.shopStore.load();

        me.columns = me.getColumns();
        me.pagingbar = me.getPagingBar();
        me.store = me.commentStore;
        me.dockedItems = [ me.pagingbar ];
        me.plugins = me.createPlugins();

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
                 * @event deleteBlogComment
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'deleteBlogComment',

                /**
                 * Event will be fired when the user clicks the accept icon in the
                 * action column
                 *
                 * @event acceptBlogComment
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'acceptBlogComment',

                /**
                 * Event will be fired when the changed the selection
                 *
                 * @event acceptBlogComment
                 * @param [integer] sm - selection model
                 * @param [object] selection
                 */
                'selectionChange'
        );
    },
    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;

        return [
            {
                header: '{s name=detail/main/comments/column/status}Status{/s}',
                dataIndex: 'active',
                renderer: me.activeColumnRenderer,
                flex: 1
            },
            {
                header: '{s name=detail/main/comments/column/date}Date{/s}',
                dataIndex: 'creationDate',
                renderer: me.dateRenderer,
                flex: 3
            },
            {
                header: '{s name=detail/main/comments/column/author}Author{/s}',
                dataIndex: 'name',
                flex: 3
            },
            {
                header: '{s name=detail/main/comments/column/headline}Headline{/s}',
                dataIndex: 'headline',
                flex: 3
            },
            {
                header: '{s name=detail/main/comments/column/shop}Shop{/s}',
                dataIndex: 'shopId',
                flex: 3,
                renderer: me.shopRenderer,
                editor: {
                    xtype: 'combobox',
                    store: me.shopStore,
                    valueField: 'id',
                    displayField: 'name'
                }
            },
            {
                xtype: 'actioncolumn',
                width: 50,
                items: me.getActionColumnItems()
            }
        ];
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
            iconCls:'sprite-plus-circle',
            cls:'addBtn',
            tooltip:'{s name=detail/main/comments/action_column/add}Accept comment{/s}',
            getClass: function(value, metadata, record) {
                if (record.get('active')) {
                    return 'x-hidden';
                }
            },
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('acceptBlogComment', view, rowIndex, colIndex, item);
            }
        });

        actionColumnData.push({
            iconCls:'sprite-minus-circle-frame',
            action:'delete',
            cls:'delete',
            tooltip:'{s name=detail/main/comments/action_column/delete}Deleted comment{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('deleteBlogComment', view, rowIndex, colIndex, item);
            }
        });

        return actionColumnData;
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
            store:me.commentStore,
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
        var me = this,
            selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the delete button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    me.fireEvent('selectionChange', sm, selections);

                }
            }
        });
        return selModel;
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
        if (record.get('creationDate') === Ext.undefined) {
            return record.get('creationDate');
        }
        return Ext.util.Format.date(record.get('creationDate')) + ' ' + Ext.util.Format.date(record.get('creationDate'), timeFormat);
    },

    /**
     * Renderer for the active flag
     *
     * @param { bool } value
     */
    activeColumnRenderer: function(value) {
        if (value) {
            return '<div class="sprite-tick"  style="width: 25px; height: 25px">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross" style="width: 25px; height: 25px">&nbsp;</div>';
        }
    },

    /**
     * @param { string } value
     * @returns { string }
     */
    shopRenderer: function(value) {
        if (value === null) {
            return '-';
        }

        var shop = this.shopStore.getById(value);

        if (shop) {
            return shop.get('name');
        }

        return value;
    },

    /**
     * @return { Ext.grid.plugin.RowEditing }
     */
    createPlugins: function () {
        return [
            Ext.create('Ext.grid.plugin.RowEditing', {
                clicksToEdit: 2,
                listeners: {
                    edit: function (editor, opts) {
                        opts.record.save();
                    }
                }
            })
        ];
    }
});
//{/block}
