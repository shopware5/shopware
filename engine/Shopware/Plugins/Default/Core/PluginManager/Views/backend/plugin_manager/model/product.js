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
 * Shopware Plugin Manager - Product Model
 * The product model of the plugin manager represents a single product of the community store.
 * The associated data like details are available over the different associations.
 */
//{block name="backend/plugin_manager/model/product"}
Ext.define('Shopware.apps.PluginManager.model.Product', {

    /**
    * Extends the standard Ext Model
    * @string
    */
    extend: 'Ext.data.Model',

    /**
    * @array
    */
    fields: [
        //{block name="backend/plugin_manager/model/product/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'supplierID', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'supplierName', type: 'string' },
        { name: 'plugin_names', type: 'array' },
        { name: 'licence', type: 'array' },
        { name: 'datum', type: 'date', dateFormat: 'Y-m-d' },
        { name: 'vote_average', type: 'float' }
    ],

    /**
     * @array
     */
    associations:[
        { type:'hasMany', model:'Shopware.apps.PluginManager.model.Detail', name:'getDetail', associationKey:'details' },
        { type:'hasMany', model:'Shopware.apps.PluginManager.model.Media', name:'getMedia', associationKey:'images' },
        { type:'hasMany', model:'Shopware.apps.PluginManager.model.Category', name:'getCategory', associationKey:'categories' },
        { type:'hasMany', model:'Shopware.apps.PluginManager.model.Addon', name:'getAddon', associationKey:'addons' },
        { type:'hasMany', model:'Shopware.apps.PluginManager.model.Attribute', name:'getAttribute', associationKey:'attributes' },
    ]
});
//{/block}

