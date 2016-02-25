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
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Shipping
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/model/category_tree"}
Ext.define('Shopware.apps.Shipping.model.CategoryTree', {
    /**
     * Extends the default extjs 4 model
     * @string
     */
    extend : 'Ext.data.Model',
     /**
     * Set an alias to make the handling a bit easier
      * @string
     */
    alias : 'model.categorymodel',
    /**
     * Defined items used by that model
     *
     * We use a reduces feature set here - just necessary fields are selected
     *
     * @array
     */
    fields : [
        //{block name="backend/shipping/model/category_tree/fields"}{/block}
        { name : 'text',     type: 'string' },
        { name : 'id',       type: 'int' },
        { name : 'parentId', type: 'int' },
        { name : 'cls',      type: 'string' },
        { name : 'checked',  type: 'string' }
    ]
});
//{/block}
