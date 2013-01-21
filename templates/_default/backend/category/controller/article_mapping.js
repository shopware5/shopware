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
 * @package    Category
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

/**
 * Shopware Controller - category management controller
 *
 * The category management controller handles the initialisation of the category tree.
 */
//{block name="backend/category/controller/article_mapping"}
Ext.define('Shopware.apps.Category.controller.ArticleMapping', {
    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',
    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        { ref: 'articleMappingView', selector: 'category-category-tabs-article_mapping' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init: function() {
        var me = this;
        me.control({
            'category-category-tabs-article_mapping textfield[action=searchArticle]':{
                change : me.onSearchArticle
            },
            'category-category-tabs-article_mapping textfield[action=searchSelectedArticle]':{
                change : me.onSearchSelectedArticle
            }
        });
    },
    /**
     * Event listener method which will be fired when the user
     * enters a search term
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchArticle: function (field, value) {
        var me = this,
            searchString = Ext.String.trim(value),
            store = me.subApplication.articleStore;
        store.filters.clear();
        store.filter('filter', searchString);
    },
    
    /**
     * Event listener method which will be fired when the user
     * enters a search term.
     * Searches locally the Selected Records
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchSelectedArticle: function (field, value) {
        var me = this,
            searchString = Ext.String.trim(value),
            articleMappingView = me.getArticleMappingView(),
            store = articleMappingView.ddSelector.toField.getStore();

        store.clearFilter();
        var searchFilter = new Ext.util.Filter({
            filterFn: function(item){
                var searchTest ,articleNameTestResult, articleDetailNumberTestResult, supplierNameTestResult;
                var escapeRegex = Ext.String.escapeRegex;
                searchTest = new RegExp(escapeRegex(searchString), 'i');

                // check match on articleName
                articleNameTestResult = searchTest.test(item.data.name);

                var detailData = item.getDetail().first();
                if (detailData) {
                    // check match on article detail number
                    articleDetailNumberTestResult = searchTest.test(detailData.get('number'));
                }

                var supplierData = item.getSupplier().first();
                if (supplierData) {
                    // check match on suplierName
                    supplierNameTestResult = searchTest.test(supplierData.get('name'));
                }
                return articleNameTestResult || articleDetailNumberTestResult || supplierNameTestResult;
            }
        });
        store.filter(searchFilter);
    }
});
//{/block}

