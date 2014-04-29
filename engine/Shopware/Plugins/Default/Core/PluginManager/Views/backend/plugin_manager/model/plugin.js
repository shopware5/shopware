/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @subpackage Main
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

/**
 * Shopware Plugin Manager - Plugin Model
 * The plugin model contains the raw data of an plugin of the shopware shop.
 */
//{block name="backend/plugin_manager/model/plugin"}
Ext.define('Shopware.apps.PluginManager.model.Plugin', {

    /**
    * Extends the standard Ext Model
    * @string
    */
    extend: 'Ext.data.Model',

   /**
    * The batch model is only a data container which contains all
    * data for the global stores in the model association data.
    * An Ext.data.Model needs one field.
    * @array
    */
    fields: [
        //{block name="backend/plugin_manager/model/plugin/fields"}{/block}
       { name: 'id', type: 'int' },
       { name: 'name', type: 'string' },
       { name: 'label', type: 'string' },
       { name: 'namespace', type: 'string' },
       { name: 'source', type: 'string' },
       { name: 'description', type: 'string' },
       { name: 'active', type: 'boolean' },
       { name: 'added', type: 'date' },
       { name: 'installed', type: 'date', useNull: true },
       { name: 'updated', type: 'date' },
       { name: 'author', type: 'string' },
       { name: 'copyright', type: 'string' },
       { name: 'license', type: 'string' },
       { name: 'version', type: 'string' },
       { name: 'support', type: 'string' },
       { name: 'changes', type: 'string' },
       { name: 'link', type: 'string' },
       { name: 'icon', type: 'string' },

       { name: 'updateVersion', type: 'string', useNull: true },

       { name: 'capabilityUpdate', type: 'boolean' },
       { name: 'capabilityEnable', type: 'boolean' },
       { name: 'capabilityInstall', type: 'boolean' },
       { name: 'capabilityDummy', type: 'boolean' },
       { name: 'capabilitySecureUninstall', type: 'boolean' },

       { name: 'configForms', type: 'array' },
       { name: 'licenses', type: 'array' },

       { name: 'removeData', type: 'boolean' }
    ],

    /**
     * @array
     */
    associations:[
        { type:'hasMany', model:'Shopware.apps.PluginManager.model.License', name:'getLicense', associationKey:'licenses' },
        { type:'hasMany', model:'Shopware.apps.PluginManager.model.Product', name:'getProduct', associationKey:'product' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy:{
        /**
         * Set proxy type to ajax
         * @string
         */
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            update: '{url action="savePlugin"}',
            destroy: '{url action="deletePlugin"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }

});
//{/block}



