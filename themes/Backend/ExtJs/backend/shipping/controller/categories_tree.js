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
 * @package    Shipping
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/controller/categories_tree}*/

/**
 * Shopware Controller - Shipping
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/controller/categories_tree"}
Ext.define('Shopware.apps.Shipping.controller.CategoriesTree', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend : 'Ext.app.Controller',

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init : function () {
        var me = this;
        /**
         * Makes sure that all checked nodes will expand
         */
        me.control({
             'shipping-view-edit-categories-tree':{
                afterrender : me.onAfterRender
            }
        });

        me.callParent(arguments);

    },
    /**
     * Detects and expand all previous selected categories
     *
     * @param { Shopware.apps.Shipping.view.edit.CategoriesTree } categoryPanel
     * @return void
     */
    onAfterRender : function(categoryPanel) {
        var tree = categoryPanel.treeSelect,
            win = tree.up('window'),
            form = win.down('form').getForm(),
            record = form.getRecord(),
            lockedCategoriesStore =  record.getCategories(),
            ids = [];

        lockedCategoriesStore.each(function(element) {
            ids.push(element.get('id'));
        });

        Ext.Ajax.request({
            url:'{url controller="Category" action="getIdPath"}',
            params: { 'categoryIds[]': ids },
            success: function(result) {
                if(!result.responseText) {
                    return ;
                }
                result =  Ext.JSON.decode(result.responseText);

                Ext.each(result.data, function(item) {
                    tree.expandPath('/1' + item, 'id');
                });
            }
        });
    }
});
//{/block}
