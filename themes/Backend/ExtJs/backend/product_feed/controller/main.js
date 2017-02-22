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
 * @package    ProductFeed
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - feed main backend module
 *
 * The feed module main controller handles the initialisation of the product feed backend list.
 */
//{block name="backend/product_feed/controller/main"}
Ext.define('Shopware.apps.ProductFeed.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    mainWindow: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;
        me.subApplication.listStore = me.getStore('List');
        me.subApplication.detailStore = me.getStore('Detail');
        me.subApplication.supplierStore = me.getStore('Supplier');
        me.subApplication.shopStore = me.getStore('Shop');
        me.subApplication.articleStore = me.getStore('Article');
        me.subApplication.availableCategoriesTree = me.getStore('Category');
        me.subApplication.comboTreeCategoryStore = me.getStore('CategoryForComboTree');

        me.mainWindow = me.getView('main.Window').create({
            listStore: me.subApplication.listStore
        });
        me.subApplication.listStore.load();

        me.callParent(arguments);
    }
});
//{/block}
