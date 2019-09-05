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
 * @package    PluginManager
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/plugin_manager/model/basket"}
Ext.define('Shopware.apps.PluginManager.model.Basket', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'grossPrice', type: 'float' },
        { name: 'netPrice', type: 'float' },
        { name: 'taxPrice', type: 'float' },
        { name: 'taxRate', type: 'string' },
        { name: 'bookingDomain', type: 'string' },
        { name: 'licenceDomain', type: 'string' },
        { name: 'licenceShopId', type: 'int' }
    ],

    associations: [
    {
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.BasketPosition',
        name: 'getPositions',
        associationKey: 'positions'
    } ,
    {
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.Domain',
        name: 'getDomains',
        associationKey: 'domains'
    } ,
    {
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.Address',
        name: 'getAddress',
        associationKey: 'address'
    }
    ]

});
//{/block}