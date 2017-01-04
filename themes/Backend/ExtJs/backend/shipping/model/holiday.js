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
//{block name="backend/shipping/model/holiday"}
Ext.define('Shopware.apps.Shipping.model.Holiday', {
    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend : 'Ext.data.Model',
    /**
     * The fields used for this model
     * @array
     */
    fields : [
        //{block name="backend/shipping/model/holiday/fields"}{/block}
        { name : 'id',  type: 'integer' },
        { name : 'name',type: 'string' }
    ],
    /**
     * If the name of the field is 'id' extjs assumes autmagical that
     * this field is an unique identifier.
     */
    idProperty : 'id',
    /**
     * Rules to validate the input at the frontend side.
     */
    validations : [
        { field : 'name', type : 'length', min : 1 }
    ],
    /**
     * Define that the holiday model belongs to the Dispatch model, which
     * means the holiday model data will be loaded over the dispatch.associations
     * @string
     */
    belongsTo:'Shopware.apps.Shipping.model.Dispatch'
});
//{/block}
