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
 * @package    Banner
 * @subpackage Category
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - Category
 *
 * Backend - Defines the category store
 *
 * This store will be loaded automatically and will just request 30 items at once.
 * It will utilize the Banner Category Model @see Shopware.apps.Banner.model.Category
 */
//{block name="backend/banner/store/category"}
Ext.define('Shopware.apps.Banner.store.Category', {
    extend : 'Shopware.store.CategoryTree',
    alias : 'store.category',
    autoLoad : false,
    pageSize : 30,
    proxy : {
        type : 'ajax',
        api : {
            read : '{url controller=banner action=getList}'
        },
        reader : {
            type : 'json',
            root: 'data'
        }
    }
});
//{/block}
