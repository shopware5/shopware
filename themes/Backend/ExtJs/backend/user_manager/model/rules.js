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
 * Shopware Backend - User Manager Rules Model
 *
 * The user manager rules model is used for the tree component which is defined in "user_manager/view/rules/tree.js".
 * It can contains the data of a single resource or the data of a single privilege.
 */
//{block name="backend/user_manager/model/rules"}
Ext.define('Shopware.apps.UserManager.model.Rules', {
   extend: 'Ext.data.Model',
   fields: [
       //{block name="backend/user_manager/model/rules/fields"}{/block}
      /**
       * unique id. If the model is a resource (type=resource) the id is equals to the resource id.
       * If the model is a privilege (type=privilege) the unique id is concat with resourceID_privilegeID
       */
       { name: 'id', type: 'int'},

      /**
       * Internal helper id which contains the id of the doctrine model
       */
       { name: 'helperId', type: 'int'},
       { name: 'name',     type: 'string'},

       /**
        * Internal helper property to differentiate between a privilege and resource model
        */
       { name: 'type',     type: 'string'},
       { name: 'pluginID', type: 'string'},

       /**
        * The following fields are helper fields to assign the resources a
        */
       { name: 'resourceId',  type: 'int'},
       { name: 'privilegeId', type: 'int'},
       { name: 'roleId',      type: 'int'},
       /**
        * Helper field for required privileges
        */
       { name: 'requirements', type: 'array' }
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
        url: '{url controller="UserManager" action="getResources"}',

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
