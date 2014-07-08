/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    ArticleList
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * shopware AG (c) 2012. All rights reserved.
 */

//{namespace name=backend/article_list/view/main}
//{block name="backend/article_list/view/main/window"}
Ext.define('Shopware.apps.ArticleList.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.articleList-main-window',
    title : '{s name=list/title}Article list{/s}',
    layout: 'border',
    width: 990,
    height: '90%',
    stateful: true,
    stateId: 'shopware-articleList-main-window',

    snippets: {
        title:         '{s name=list/title}Article overview{/s}',
        categoryTitle: '{s name=list/category_title}Categories{/s}',
        filterTitle:   '{s name=list/filter_title}Filter{/s}',

        noFilter:      '{s name=list/no_filter}No filter{/s}',
        notInStock:    '{s name=list/not_in_stock}Not in stock{/s}',
        noCategory:    '{s name=list/no_category}No categories{/s}',
        noImage:       '{s name=list/no_image}No images{/s}'
    },

    initComponent: function() {
        var me = this;

        me.title = me.snippets.title;

        me.addEvents(
            /**
             * @event
             * @param [Ext.view.View] view - the view that fired the event
             * @param [Ext.data.Model] record
             */
            'categoryChanged'
        );

        me.categoryStore = Ext.create('Shopware.store.CategoryTree');

        me.items = [{
            xtype: 'articleList-main-grid',
            articleStore: me.articleStore,
            region: 'center'
        }];

        me.sidebarPanel = Ext.create('Ext.panel.Panel', {
            title: me.snippets.categoryTitle,
            collapsible: true,
            width: 230,
            layout: {
                type: 'vbox',
                pack: 'start',
                align: 'stretch'
            },
            region: 'west',
            items: [
                me.createTree(),
                me.createFilterPanel()
            ]
        });

        me.items.push(me.sidebarPanel);

        me.callParent(arguments);
    },

    createFilterPanel: function() {
        var me = this;

        return new Ext.create('Ext.form.Panel', {
            title: me.snippets.filterTitle,
            bodyPadding: 5,
            items: [{
                xtype: 'radiogroup',
                listeners: {
                    change: {
                        fn: function(view, newValue, oldValue) {
                            var me    = this,
                                store =  me.articleStore;

                            store.getProxy().extraParams.filterBy = newValue.filter;
                            store.load();

                        },
                        scope: me
                    }
                },
                columns: 1,
                vertical: true,
                items: [
                    { boxLabel: me.snippets.noFilter, name: 'filter', inputValue: 'none', checked: true  },
                    { boxLabel: me.snippets.notInStock, name: 'filter', inputValue: 'notInStock'  },
                    { boxLabel: me.snippets.noCategory, name: 'filter', inputValue: 'noCategory' },
                    { boxLabel: me.snippets.noImage, name: 'filter', inputValue: 'noImage' }
                ]
            }]


        });
    },

    /**
     * Creates the category tree
     *
     * @return [Ext.tree.Panel]
     */
    createTree: function() {
        var me = this;

        var tree = Ext.create('Ext.tree.Panel', {
            rootVisible: true,
            flex: 1,
            expanded: true,
            useArrows: false,
            store: me.categoryStore,
            root: {
                text: me.snippets.categoryTitle,
                expanded: true
            },
            listeners: {
                itemclick: {
                    fn: function(view, record) {
                        var me    = this,
                            store =  me.articleStore;

                        if (record.get('id') === 'root') {
                            store.getProxy().extraParams.categoryId = null;
                        } else {
                            store.getProxy().extraParams.categoryId = record.get('id');
                        }

                        //scroll the store to first page
                        store.currentPage = 1;
                        store.load({
                            callback: function() {
                            }
                        });
                    },
                    scope: me
                }
            }
        });

        return tree;
    }
});
//{/block}
