/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @package    PluginManager
 * @subpackage Main
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

/**
 * Shopware Plugin Manager - Community Store
 * The community store contains a result of the store categories with an offset of products of the category.
 * The pageSize property of this store is used for the product limit.
 * It is used for the community store tab.
 */
//{block name="backend/plugin_manager/store/community"}
Ext.define('Shopware.apps.PluginManager.store.Community', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend:'Ext.data.Store',
    /**
     * Define the used model for this store
     * @string
     */
    model:'Shopware.apps.PluginManager.model.Category',
    /**
     * True to defer any sorting operation to the server. If false, sorting is done locally on the client.
     */
    remoteSort: true,

    /**
     * True to defer any filtering operation to the server. If false, filtering is done locally on the client.
     */
    remoteFilter: true,

    /**
     * The number of records considered to form a 'page'. This is used to power the built-in paging using the
     * nextPage and previousPage functions when the grid is paged using a PagingScroller Defaults to 25.
     */
    pageSize: 6,
    /**
     * Configure the data communication
     * @object
     */
    proxy:{
        /**
         * Set proxy type to ajax
         * @string
         */
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        url: '{url controller=Store action="communityList"}',

        /**
         * Configure the data reader
         * @object
         */
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }
});
//{/block}

