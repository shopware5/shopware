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
 * @package    Banner
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/banner/view/main}*/

/**
 * Shopware UI - Banner View Main Panel
 *
 * View component which features the main panel
 * of the module. It displays the banners.
 */
//{block name="backend/banner/view/main/panel"}
Ext.define('Shopware.apps.Banner.view.main.Panel', {
    extend: 'Ext.container.Container',
    alias : 'widget.banner-view-main-panel',
    layout: 'border',
    style: 'background: #fff',

    /**
     * Dummy category id - will be set later
     * @integer
     */
    categoryId:0,

    /**
     * Initialize the view.main.List and defines the necessary
     * default configuration
     * @return void
     */
    initComponent : function () {
        var me = this;

        me.categoryTree = me.getCategoryTree();
        me.bannerList = me.getBannerList();
        me.toolbar = me.getBannerToolbar();
        me.items = [ me.categoryTree, me.bannerList, me.toolbar ];

        me.callParent(arguments);
    },
    /**
     * Returns the toolbar used to add or delete a banner
     *
     * @return Ext.toolbar.Toolbar
     */
    getBannerToolbar : function() {
        return Ext.create('Ext.toolbar.Toolbar', {
            region: 'north',
            ui: 'shopware-ui',
            items: [
                /*{if {acl_is_allowed privilege=create}}*/
                {
                    iconCls : 'sprite-plus-circle',
                    text : '{s name=view/main_add}Add{/s}',
                    action : 'addBanner',
                    disabled : true
                },
                /* {/if} */
                /*{if {acl_is_allowed privilege=delete}}*/
                {
                    iconCls : 'sprite-minus-circle',
                    text : '{s name=view/main_delete}Delete{/s}',
                    disabled : true,
                    action : 'deleteBanner'
                },
                /* {/if} */
                /*{if {acl_is_allowed privilege=update}}*/
                {
                    iconCls : 'sprite-pencil',
                    text : '{s name=view/main_edit}Edit{/s}',
                    disabled : true,
                    action : 'editBanner'
                }
                /*{/if}*/
            ]
        });
    },
    /**
     * The Banner data view
     *
     * @return Ext.Panel
     */
    getBannerList : function() {
        var me = this;

        me.dataView = Ext.create('Ext.view.View', {
            store: me.bannerStore,
            region: 'center',
            tpl: me.getBannerListTemplate(),
            multiSelect: true,
            height: '100%',//just for the selector plugin
            trackOver: true,
            overItemCls: 'x-item-over',
            itemSelector: 'div.thumb-wrap',
            emptyText: '',
            plugins: [ Ext.create('Ext.ux.DataView.DragSelector') ],

            /**
            * Data preparation for the data view
            * eg. truncate the description to max 27 chars
            *
            * @param data
            */
             prepareData : function(data) {
                Ext.apply(data, {
                    description : Ext.util.Format.ellipsis(data.description, 27),
                    img         : data.image,
                   id           : data.id
                });
                return data;
            }
        });

        return Ext.create('Ext.panel.Panel', {
            cls: 'banner-images-view',//only for the css styling
            region: 'center',
            unstyled: true,
            style: 'border-top: 1px solid #c7c7c7',
            autoScroll: true,
            items: [ me.dataView ]
        });
    },
    /**
     * Returns the ExtJS Template for the banner display
     *
     * @return array of strings
     */
    getBannerListTemplate : function() {
        var basePath = '';
        return [
            '<tpl for=".">',
                '<div class="thumb-wrap" id="{literal}{id}{/literal}">',
                    '<div class="thumb"><img src="{literal}{image}{/literal}" title="{literal}{description}{/literal}"></div>',
                    '<span class="x-editable">{literal}{description}{/literal}</span>',
                '</div>',
            '</tpl>',
            '<div class="x-clear"></div>'
        ];
    },

    /**
     * Builds and returns the category tree
     *
     * @return Ext.tree.Panel
     */
    getCategoryTree : function() {
        return Ext.create('Ext.tree.Panel', {
            title: '{s name=view/tree_title}Catergories{/s}',
            collapsible: true,
            store: this.categoryStore,
            region: 'west',
            loadMask: false,
            width: 180,
            rootVisible: false,
            useArrows: false
        });
    }
});
//{/block}
