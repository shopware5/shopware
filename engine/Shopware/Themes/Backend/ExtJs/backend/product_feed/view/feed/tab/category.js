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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/product_feed/view/feed}

/**
 * Shopware UI - ProductFeed detail main window.
 *
 * Displays all Detail Information
 */
//{block name="backend/product_feed/view/feed/tab/category"}
Ext.define('Shopware.apps.ProductFeed.view.feed.tab.Category', {
    extend:'Ext.container.Container',
    alias:'widget.product_feed-feed-tab-category',
    title:'{s name=tab/title/category}Blocked categories{/s}',
    padding: 10,
    cls: 'shopware-toolbar',
    layout: 'anchor',

    /**
     * Initialize the controller and defines the necessary default configuration
     * @return void
     */
    initComponent : function() {
        var me = this,
            ids = [];
        if(me.record && me.record.getCategoriesStore) {
            var lockedCategoriesStore = me.record.getCategoriesStore;
            lockedCategoriesStore.each(function(element) {
                ids.push(element.get('id'));
            });
        }
        // make sure we have at least one element.
        if(ids.length == 0) {
            ids.push('0');
        }
        me.availableCategoriesTree.getProxy().extraParams = {
            'preselected[]' : ids
        };
        var tree = me.getTreeSelect(ids, me.availableCategoriesTree);

        me.items = [tree];
        me.callParent(arguments);
    },
    /**
     * Returns the selection box
     *
     * @param ids array of integers
     * @return Ext.tree.Panel
     */
    getTreeSelect : function(ids, store) {
        return Ext.create('Ext.tree.Panel', {
            name: 'categoryIds',
            store: store,
            displayField: 'name',
            rootVisible: false,
            useArrows: true,
            autoscroll: true,
            height: 270,
            queryMode: 'remote',
            expanded: true,
            flex: 1,
            root: {
                id: 1
            }
        });
    }
});
//{/block}
