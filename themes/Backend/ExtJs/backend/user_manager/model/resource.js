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
 * @package    UserManager
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Backend - User Manager Resource Model
 *
 * The user manager resource model represents a row the s_core_acl_resources.
 * It is used to create or delete a single resource.
 */
//{block name="backend/user_manager/model/resource"}
Ext.define('Shopware.apps.UserManager.model.Resource', {
    /**
     * Define that the privilege model is an extension of the Ext.data.Model
     */
    extend: 'Ext.data.Model',

    /**
     * The field property contains all model fields.
     * @array
     */
    fields: [
        //{block name="backend/user_manager/model/resource/fields"}{/block}
        { name: 'id',     type: 'int'},
        { name: 'name',     type: 'string'},
        { name: 'pluginID',     type: 'string'}
    ],

    /**
    * Configure the data communication
    * @object
    */
    proxy: {
        /**
         * Set proxy type to ajax
         * @string
         */
        type: 'ajax',

        /**
         * Specific urls to call on CRUD action methods "create", "read", "update" and "destroy".
         * @object
         */
        api: {
            create: '{url action="saveResource"}',
            destroy: '{url action="deleteResource"}'
        },

        /**
         * The Ext.data.reader.Reader to use to decode the server's
         * response or data read from client. This can either be a Reader instance,
         * a config object or just a valid Reader type name (e.g. 'json', 'xml').
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
