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
 * Displays all List Blog Information
 */
/**
 * Default blog list view. Extends a grid view.
 */
//{block name="backend/blog/view/blog/detail/comments"}
Ext.define('Shopware.apps.Blog.view.blog.detail.Comments', {
    extend:'Ext.panel.Panel',
    border: false,
    alias:'widget.blog-blog-detail-comments',
    region:'center',
    autoScroll:false,
    layout:'border',
    disabled:true,
    ui:'shopware-ui',

    /**
     * Initialize the Shopware.apps.Blog.view.main.List and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.items = [
            {
                xtype:'blog-blog-detail-comments-grid',
                commentStore:me.commentStore
            },
            {
                xtype:'blog-blog-detail-comments-info_panel'
            }
        ];
        me.toolbar = me.getToolbar();
        me.dockedItems = [ me.toolbar ];

        me.callParent(arguments);
    },
    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui:'shopware-ui',
            items:[
        /* {if {acl_is_allowed privilege=comments}} */
                {
                    iconCls:'sprite-plus-circle',
                    text:'{s name=detail/main/comments/button/add}Accept selected comments{/s}',
                    disabled:true,
                    action:'acceptSelectedComments'
                },
                {

                    iconCls:'sprite-minus-circle-frame',
                    text:'{s name=detail/main/comments/button/delete}Delete selected comments{/s}',
                    disabled:true,
                    action:'deleteSelectedComments'
                },
        /* {/if} */
                '->',
                {
                    xtype:'textfield',
                    name:'searchfield',
                    action:'searchBlogComments',
                    width:170,
                    cls:'searchfield',
                    enableKeyEvents:true,
                    checkChangeBuffer:500,
                    emptyText:'{s name=detail/main/comments/field/search}Search...{/s}'
                },
                { xtype:'tbspacer', width:6 }
            ]
        });
    }
});
//{/block}
