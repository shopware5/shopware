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
 * The country model represents a data row of the s_core_countries or the
 * Shopware\Models\Country\Country doctrine model.
 */
//{block name="backend/base/model/country"}
Ext.define('Shopware.apps.Base.model.Country', {

    /**
     * Defines an alternate name for this class.
     */
    alternateClassName: 'Shopware.model.Country',

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Shopware.data.Model',

    /**
     * unique id
     * @int
     */
    idProperty: 'id',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/base/model/country/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'iso', type: 'string' },
        { name: 'isoName', type: 'string' },
        { name: 'position', type: 'int' },
        { name: 'active', type: 'boolean' },
        { name: 'forceStateInRegistration', type: 'boolean' },
        { name: 'displayStateInRegistration', type: 'boolean' },
        { name: 'allowShipping', type: 'boolean' }
    ]

});
//{/block}
