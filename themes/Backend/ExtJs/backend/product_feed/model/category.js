/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    ProductFeed
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - ProductFeed backend module.
 *
 * The category model is used for the category filter tree.
 */
//{block name="backend/product_feed/model/category"}
Ext.define('Shopware.apps.ProductFeed.model.Category', {
    /**
     * Extends the default extjs 4 model
     * @string
     */
    extend: 'Shopware.apps.Base.model.Category',
    /**
     * Defined items used by that model
     *
     * We use a reduces feature set here - just necessary fields are selected
     *
     * @array
     */
    fields: [
        //{block name="backend/product_feed/model/category/fields"}{/block}
        { name: 'checked',  type: 'boolean' }
    ],
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller=category action=getList}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
