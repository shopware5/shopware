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
 * Shopware Plugin Manager - Category Model
 * The category model of the plugin manager contains the raw data of a single community
 * store category.
 */
//{block name="backend/plugin_manager/model/category"}
Ext.define('Shopware.apps.PluginManager.model.Category', {

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
        //{block name="backend/plugin_manager/model/category/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'parent', type: 'int' },
        { name: 'description', type: 'string' },
        { name: 'position', type: 'int' },
        { name: 'children', type: 'int' },
        { name: 'selected', type: 'boolean', defaultValue: false },
        { name: 'products', type: 'array' }
    ],

    /**
     * @array
     */
    associations:[
        { type:'hasMany', model:'Shopware.apps.PluginManager.model.Product', name:'getProduct', associationKey:'products' }
    ]

});
//{/block}

