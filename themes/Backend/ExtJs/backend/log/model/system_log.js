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
 * @package    Systeminfo
 * @subpackage Model
 * @version    $Id$
 * @author     shopware AG
 */

//{block name="backend/log/model/system_log"}
Ext.define('Shopware.apps.Log.model.SystemLog', {

    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/log/model/system_log/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'date', type: 'date' },
        { name: 'channel', type: 'string' },
        { name: 'level', type: 'string' },
        { name: 'message', type: 'string' },
        {
            name: 'code',
            type: 'string',
            convert: function (value, record) {
                if (record.raw.context && record.raw.context.code) {
                    return record.raw.context.code;
                }
                return '';
            }
        }, {
            name: 'context',
            type: 'string',
            convert: function (value, record) {
                if (value && (Ext.isObject(value) || Ext.isArray(value))) {
                    return JSON.stringify(value, null, 2);
                }
                return '';
            }
        }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',
        api: {
            read: '{url action="getLogList"}'
        },
        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'count'
        }
    }
});
//{/block}
