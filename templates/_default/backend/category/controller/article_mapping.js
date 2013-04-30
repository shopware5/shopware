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
        { ref: 'articleMappingView', selector: 'category-category-tabs-article_mapping' },
        { ref: 'addButton', selector: 'category-category-tabs-article_mapping button[action=add]' },
        { ref: 'removeButton', selector: 'category-category-tabs-article_mapping button[action=remove]' }
    ],

    /**
     * Initializies the necessary event listener for the controller.
     *
     * @returns { Void }
     */
    init: function() {
        var me = this;

        me.control({
            'category-category-tabs-article_mapping': {
                'search': me.onSearch,
                'add': me.onAddProducts,
                'remove': me.onRemoveProducts
            },
            'category-category-tabs-article_mapping grid': {
                'selectionchange': me.onSelectionChange
            }
        });
    },

    /**
     * Event listener method which will be fired when the user selects an entry
     * in either the `toGrid` or the `fromGrid`.
     *
     * The method handles the enables / disables the buttons in the middle column
     * of the view and deselects all entries in the inactive grid.
     *
     * @param { Ext.selection.CheckboxModel } selModel
     * @param { Array } selection
     * @returns { boolean }
     */
    onSelectionChange: function(selModel, selection) {
        var me = this,
            view = me.getArticleMappingView(),
            activeGrid = selModel.view.panel, inactiveGrid,
            activeBtn, inactiveBtn;

        // Prevent the grid to get a little sluggish
        if(!selection.length) {
            return false;
        }
        
        inactiveGrid = (activeGrid.internalTitle === 'from') ? view.toGrid : view.fromGrid;

        if(activeGrid.internalTitle === 'from') {
            activeBtn = me.getAddButton();
            inactiveBtn = me.getRemoveButton();
        } else {
            activeBtn = me.getRemoveButton();
            inactiveBtn = me.getAddButton();
        }

        // Enable / disable buttons
        activeBtn.setDisabled(false);
        inactiveBtn.setDisabled(true);

        inactiveGrid.getSelectionModel().deselectAll(true);

        return true;
    },

    /**
     * Triggers a search by using an `extraParam` on the
     * associated store of the active grid.
     *
     * @param { String } value
     * @param { Ext.grid.Panel } activeGrid
     */
    onSearch: function(value, activeGrid) {
        var store = activeGrid.getStore();

        value = Ext.String.trim(value);
        store.currentPage = 1;

        store.getProxy().extraParams.search = (!value.length) ? '' : value;
        store.load();
    },

    onAddProducts: function(scope) {
        var me = this, activeGrid = scope.fromGrid,
            inactiveGrid = scope.toGrid,
            store = activeGrid.getStore(),
            inactiveStore = inactiveGrid.getStore(),
            selection = activeGrid.getSelectionModel().getSelection(),
            ids = [];

        if(!selection.length) {
            return false;
        }

        Ext.each(selection, function(sel) {
            ids.push(sel.data.id);
        });

        store.remove(selection);
        inactiveStore.add(selection);
    },

    onRemoveProducts: function(scope) {
        var activeGrid = scope.toGrid;
        console.log(arguments);
    },

    _sendRequest: function(action, ids) {
    }
});
//{/block}

