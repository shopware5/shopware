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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/view/edit/categories_tree}*/

/**
 * Shopware UI - Shipping Costs
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/view/edit/categories_tree"}
Ext.define('Shopware.apps.Shipping.view.edit.CategoriesTree', {
     /**
     * Based on Ext.form.Panel
     */
    extend : 'Ext.form.Panel',
    /**
     * Alias for easy creation
     * @string
     */
    alias : 'widget.shipping-view-edit-categories-tree',

    /**
     * Name of this view
     * @string
     */
    name : 'shipping-view-edit-categories-tree',
    /**
     * Title as shown in the tab from the panel
     * @string
     */
    title : '{s name=category_selection_tab_title}Lock this category.{/s}',
    /**
     * Display the the contents of this tab immediately
     * @boolean
     */
    autoShow : true,
    /**
     * Use the full height
     * @string
     */
    height : '200px',
    /**
     * No borders
     * @integer
     */
    border : 0,
    /**
     * Autoscrolling enabled
     * @boolean
     */
    autoScroll : true,

    /**
     * Country tree
     * @Ext.tree.Panel
     */
    treeSelect : null,

    /**
     * Initialize the controller and defines the necessary default configuration
     * @return void
     */
    initComponent : function() {
        var me = this,
            lockedCategoriesStore =  me.record.getCategories(),
            ids = [];

        lockedCategoriesStore.each(function(element) {
            ids.push(element.get('id'));
        });

        // make sure we have at least on element.
        if (ids.length === 0) {
            ids.push('0');
        }

        me.availableCategoriesTree.getProxy().extraParams = {
            'preselected[]' : ids
        };

        me.items = [ me.getTreeSelect(ids, me.availableCategoriesTree) ];
        me.callParent(arguments);
    },

    /**
     * Returns the selection box
     *
     * @param { Array } ids array of integers
     * @param { Ext.data.Store } store
     * @return { Ext.tree.Panel }
     */
    getTreeSelect : function(ids, store) {
        var me = this;

        me.treeSelect = Ext.create('Ext.tree.Panel', {
            name: 'treeselect',
            store:  store,
            rootVisible: false,
            useArrows: true,
            autoscroll: true,
            height: 200,
            queryMode: 'remote',
            expanded: true,
            flex: 1,
            root: {
                id: 1
            }
        });

        return me.treeSelect;
    }
});
//{/block}
