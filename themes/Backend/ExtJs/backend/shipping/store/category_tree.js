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
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - Shipping
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/store/category"}
Ext.define('Shopware.apps.Shipping.store.CategoryTree', {
    /**
     * Parent Object
     * @string
     */
    extend : 'Ext.data.TreeStore',
    /**
     * Store to use
     * @string
     */
    alias : 'store.category',
    /**
     * USe remote filtering
     * @boolean
     */
    remoteFilter: true,
    /**
     * Do not load the data on your own
     * @boolean
     */
    autoLoad : false,
    /**
     * Default page size is 30 items
     * @integer
     */
    pageSize : 30,
    /**
     * Model to use for this store
     * @string
     */
    model : 'Shopware.apps.Banner.model.Category',
    /**
     * Proxy config object.
     * @object
     */
    proxy : {
        type : 'ajax',
         /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api : {
            read : '{url controller=category action=getList}',
            create : '{url controller=category action=createCategory}',
            update : '{url controller=category action=updateCategory}',
            destroy : '{url controller=category action=deleteCategory}'
        },
        /**
         * Configure the data reader
         * @object
         */
        reader : {
            type : 'json',
            root: 'data'
        }
    }
});
//{/block}
