/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Countries
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/site/countries/nodes"}
Ext.define('Shopware.apps.Countries.model.Countries', {
	extend: 'Ext.data.Model',
    fields: [
		//{block name="backend/site/countries/nodes/fields"}{/block}
        { name : 'id', type: 'string' },
        { name : 'text', type: 'string' },
        { name : 'databaseId', type: 'string' },
        { name : 'hasCountriesAssigned', type: 'boolean' }
    ],

	proxy: {
		type: 'ajax',
        api: {
            read: '{url action="getNodes"}',
            create: '{url action="saveSite"}',
            update: '{url action="saveSite"}',
            destroy: '{url action="deleteCountry"}'
        },
		reader: {
			type: 'json',
			root: 'nodes'
		}
	},

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Countries.model.CountryAttribute', name: 'getAttributes', associationKey: 'attribute'}
    ]
});
//{/block}