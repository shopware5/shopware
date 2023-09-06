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
 * @package    PluginManager
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/plugin_manager/model/licence"}
Ext.define('Shopware.apps.PluginManager.model.Licence', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'id', type: 'int' },
        { name: 'label', type: 'string' },
        { name: 'technicalName', type: 'string' },
        { name: 'iconPath', type: 'string' },
        { name: 'shop', type: 'string' },
        { name: 'subscription', type: 'string' },
        { name: 'creationDate', type: 'datetime' },
        { name: 'expirationDate', type: 'datetime' },
        { name: 'licenseKey', type: 'string' },
        { name: 'isLicenseCheckEnabled', type: 'boolean', defaultValue: false, persist: false },
        { name: 'binaryLink', type: 'string' },
        { name: 'binaryVersion', type: 'string' },

        { name: 'priceColumn', type: 'string' }
    ],

    associations: [{
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.Price',
        name: 'getPrice',
        associationKey: 'priceModel'
    }]
});
//{/block}
