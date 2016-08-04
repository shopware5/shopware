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
 * @package    Log
 * @subpackage Model
 * @version    $Id$
 * @author VIISON GmbH
 */

/**
 * Shopware - Core log model
 */
//{block name="backend/log/model/log/core"}
Ext.define('Shopware.apps.Log.model.log.Core', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/log/model/log/core/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'timestamp', type: 'date' },
        { name: 'level', type: 'string' },
        {
            name: 'message',
            type: 'string',
            convert: function(value, record) {
                return (value || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }
        }, {
            name: 'context',
            type: 'string',
            convert: function(value, record) {
                if (value && (Ext.isObject(value) || Ext.isArray(value))) {
                    return JSON.stringify(value, null, 2).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                }

                return '';
            }
        }, {
            name: 'exception',
            type: 'auto', // exception is an object with several properties
            convert: function(value, record) {
                if (value && (Ext.isObject(value) || Ext.isArray(value))) {
                    var jsonString = JSON.stringify(value).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    return JSON.parse(jsonString);
                }

                return value;
            }
        }, {
            name: 'rawLine',
            type: 'string',
            convert: function(value, record) {
                return (value || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }
        }
    ]
});
//{/block}
