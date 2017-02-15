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
 * @package    Article
 * @subpackage Category
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Article backend module
 *
 * todo@all: Documentation
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/controller/category"}
Ext.define('Shopware.apps.Article.controller.Category', {
    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Contains all snippets for the component.
     * @object
     */
    snippets: {
        alreadyExist: {
            title: '{s name=category/already_exist/title}Failed{/s}',
            message: '{s name=category/already_exist/message}Category: [0] has already been assigned{/s}',
            caller: '{s name=category/already_exist/caller}Article{/s}'
        }
    },
    /**
     * Set component references for easy access
     * @array
     */
    refs:[
        { ref:'categoryTree', selector:'article-detail-window article-category-drop-zone' },
        { ref:'categoryDropZone', selector:'article-detail-window article-category-tree' },
        { ref:'categoryGrid', selector:'article-detail-window article-category-list' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @params orderId - The main controller can handle a orderId parameter to open the order detail page directly
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'article-detail-window article-category-drop-zone': {
                addCategories: me.onAddCategory
            },
            'article-detail-window article-category-list': {
                removeCategories: me.onRemoveCategories
            },
            'article-detail-window article-category-tree': {
                addCategory: me.onAddCategory
            }
        });
        me.callParent(arguments);
    },

    /**
     * Event listener function which fired when the user want to remove an assigned category.
     * The event can be fired over the grid action column, or over the toolbar button
     * on the category tab.
     *
     * @param array categories
     * @return boolean
     */
    onRemoveCategories: function(categories) {
        var me = this,
            grid = me.getCategoryGrid(),
            gridStore = grid.getStore();

        if (!Ext.isArray(categories)) {
            return true;
        }
        gridStore.remove(categories);
        return true;
    },

    /**
     * Event listener function which fired when the user want to add a category over drag and drop
     * in the category tab.
     *
     * @param categories
     */
    onAddCategory: function(categories) {
        var me = this, message,
            grid = me.getCategoryGrid(),
            gridStore = grid.getStore();

        if (!Ext.isArray(categories)) {
            return true;
        }

        Ext.each(categories, function(category) {
            if (category instanceof Ext.data.Model && category.get('allowDrag')) {
                if (!gridStore.getById(category.get('id'))) {
                    gridStore.add(category);
                } else {
                    message = Ext.String.format(me.snippets.alreadyExist.message, category.get('name'));
                    Shopware.Notification.createGrowlMessage(me.snippets.alreadyExist.title, message, me.snippets.alreadyExist.caller);
                }
            }
        });
    }

});
//{/block}
