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

/**
 * Shopware Backend - States model
 *
 * todo@all: Documentation
 */
//{block name="backend/countries/model/states"}
Ext.define('Shopware.apps.Countries.model.States', {
	extend: 'Ext.data.Model',
	fields: [
		//{block name="backend/countries/model/states/fields"}{/block}
		'id', 'name','shortCode','position','active'],
	proxy: {
		type: 'ajax',
		api: {
			read: '{url controller="Countries" action="getStates"}',
			create: '{url controller="Countries" action="updateState"}',
			update: '{url controller="Countries" action="updateState"}',
			destroy: '{url controller="Countries" action="deleteState"}'
		},
		reader: {
			type: 'json',
			root: 'data',
            totalProperty:'total'
		}
	},
    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Countries.model.StateAttribute', name: 'getAttributes', associationKey: 'attribute'}
    ]
});
//{/block}