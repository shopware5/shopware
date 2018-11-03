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
 * Shopware Model - Version list systeminfo backend module.
 * This model represents a single shopware-config
 *
 * @link http://www.shopware.de/
 * @license http://www.shopware.de/license
 * @package Systeminfo
 * @subpackage Version
 */

//{block name="backend/systeminfo/model/version"}
Ext.define('Shopware.apps.Systeminfo.model.Version', {

    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/systeminfo/model/version/fields"}{/block}
        { name: 'name', type: 'string' },
        { name: 'version', type: 'string' },
        { name: 'result', type: 'boolean' }
    ],
    /**
    * Configure the data communication
    * @object
    */
    proxy: {
        type: 'ajax',
        api: {
            //read out all versions
            read: '{url controller="systeminfo" action="getVersionList"}'
        },
        /**
        * Configure the data reader
        * @object
        */
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
