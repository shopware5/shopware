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
 * @package    Base
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Global Stores and Models
 *
 * The shop model represents a data row of the s_core_shops or the
 * Shopware\Models\Shop\Shop doctrine model.
 */
//{block name="backend/base/model/shop"}
Ext.define('Shopware.apps.Base.model.Shop', {

    /**
     * Defines an alternate name for this class.
     */
    alternateClassName: 'Shopware.model.Shop',

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/base/model/shop/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'default', type: 'boolean' },
        { name: 'localeId', type: 'int' },
        { name: 'categoryId', type: 'int' },
        { name: 'name', type: 'string' }
    ]
});
//{/block}
