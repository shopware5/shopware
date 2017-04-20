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
 * @author shopware AG
 */

/**
 * Shopware - Log model
 *
 * This model represents a single log of s_core_log.
 */
//{block name="backend/log/model/log"}
Ext.define('Shopware.apps.Log.model.Log', {
    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend: 'Ext.data.Model',
    /**
    * The fields used for this model
    * @array
    */
    fields: [
        //{block name="backend/log/model/log/fields"}{/block}
        'id',
        'type',
        'key',
        'text',
        {   name: 'date',
            type: 'date',
            dateFormat:'Y-m-d'
        },
        'user',
        'ip_address',
        'user_agent',
        'value4'
    ],
    /**
    * Configure the data communication
    * @object
    */
    proxy: {
        type: 'ajax',
        /**
        * Configure the url mapping for the different
        * @object
        */
        api: {
            //read out all articles
            read: '{url controller="log" action="getLogs"}',
            destroy: '{url controller="log" action="deleteLogs"}'
        },
        /**
        * Configure the data reader
        * @object
        */
        reader: {
            type: 'json',
            root: 'data',
            //total values, used for paging
            totalProperty: 'total'
        }
    }
});
//{/block}
