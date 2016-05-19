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
 */

//{namespace name=backend/article_list/main}
//{block name="backend/article_list/view/main/category_tree"}
Ext.define('Shopware.apps.ArticleList.view.main.CategoryTree', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.multi-edit-category-tree',
    layout: 'fit',
    title: '{s name=categories}Categories{/s}',

    initComponent: function () {
        var me = this;

        me.items = me.getPanels();

        me.addEvents(
            'filterByCategory',
            'showVariants'
        );

        me.callParent(arguments);
    },

    /**
     * Returns the tree panel with and a toolbar
     */
    getPanels: function () {
        var me = this;

        me.treePanel = Ext.create('Ext.panel.Panel', {
            border: false,
            layout: {
                type: 'vbox',
                pack: 'start',
                align: 'stretch'
            },
            items: [
                me.getToolbar(),
                me.createTree()
            ]
        });

        return [me.treePanel];
    },

    /**
     * Creates the toolbar with the "show variants" checkbox
     *
     * @returns Ext.toolbar.Toolbar
     */
    getToolbar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                {
                    xtype: 'checkbox',
                    boxLabel: '{s name=list/Variants}Show variants{/s}',
                    name: 'displayVariants',
                    uncheckedValue: false,
                    inputValue: true,
                    listeners: {
                        'change': function (field, newValue) {
                            var tree = me.up().down('treepanel'),
                                    selection = tree.getSelectionModel(),
                                    categoryId;

                            if (selection.selected.items.length <= 0) {
                                categoryId = 0;
                            } else {
                                categoryId = selection.selected.items[0].get('id');
                            }

                            me.fireEvent('showVariants', newValue);
                            me.fireEvent('filterByCategory', categoryId, newValue);
                        }
                    }
                }
            ]
        });


    },

    /**
     * Creates the category tree
     *
     * @return [Ext.tree.Panel]
     */
    createTree: function () {
        var me = this,
                tree;

        me.categoryStore = Ext.create('Shopware.store.CategoryTree');

        tree = Ext.create('Ext.tree.Panel', {
            border: false,
            rootVisible: true,
            expanded: true,
            useArrows: false,
            layout: 'fit',
            flex: 1,
            store: me.categoryStore,
            root: {
                text: '{s name=categories}Categories{/s}',
                expanded: true
            },
            listeners: {
                itemclick: {
                    fn: function (view, record) {
                        var me = this,
                                showVariants,
                                categoryId = record.get('id') === 'root' ? 0 : record.get('id');

                        showVariants = me.up().down('checkbox').getValue();

                        me.fireEvent('filterByCategory', categoryId, showVariants);
                    }
                },
                scope: me
            }
        });

        return tree;
    }

});
//{/block}
