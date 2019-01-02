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

/**
 * The category_filter controllers handels filtering by category
 */
//{namespace name=backend/article_list/main}
//{block name="backend/article_list/controller/category_filter"}
Ext.define('Shopware.apps.ArticleList.controller.CategoryFilter', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    refs: [
        { ref:'mainGrid', selector:'multi-edit-main-grid' },
        { ref:'categoryTree', selector:'multi-edit-category-tree' }
    ],

    /**
     * Init the controller and register controlls
     */
    init: function () {
        var me = this;

        me.control({
            'multi-edit-category-tree': {
                'filterByCategory': me.onFilterByCategory,
                'showVariants': me.onShowVariants
            }
        });

        me.callParent(arguments);
    },

    /**
     * Dynamically create a filter query based on the current category selection
     *
     * @param categoryId
     * @param showVariants
     */
    onFilterByCategory: function(categoryId, showVariants) {
        var me = this,
            filterString = '';

        if (!showVariants) {
            filterString = ' ISMAIN AND ';
        }

        if (categoryId > 0) {
            filterString += '(CATEGORY.PATH = "%|' + categoryId + '|%" OR CATEGORY.ID = ' + categoryId + ')';
        } else {
            filterString += 'ARTICLE.ID > 0';
        }

        me.getController('Suggest').loadFilter(
            filterString,
            me.getFilterNameByConfig(categoryId, showVariants),
            {
                categoryId: categoryId,
                showVariants: showVariants
            }
        );
    },

    onShowVariants: function(isActive) {
        var me = this;
        me.getMainGrid().detailActiveColumn.setVisible(isActive);
    },

    /**
     * Generates a translatable description of an auto-generated filter based on the configuration
     *
     * @param categoryId
     * @param showVariants
     */
    getFilterNameByConfig: function(categoryId, showVariants) {
        var me = this,
            snippet;

        if (!categoryId) {
            snippet = '{s name=allProducts}all products{/s}';
        } else {
            snippet = Ext.String.format('{s name=productsInCategory}products in category [0]{/s}', categoryId);
        }

        if (showVariants) {
            snippet += ' ' + '{s name=withVariants}with variants{/s}';
        }

        return snippet;
    }
});
//{/block}
