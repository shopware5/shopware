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
 */
//{block name="backend/plugin_manager/model/license"}
Ext.define('Shopware.apps.PluginManager.model.License', {

    /**
    * Extends the standard Ext Model
    * @string
    */
    extend: 'Ext.data.Model',

   /**
    * @array
    */
    fields: [
        //{block name="backend/plugin_manager/model/license/fields"}{/block}
       { name: 'id', type: 'int' },
       { name: 'module', type: 'string' },
       { name: 'host', type: 'string' },
       { name: 'label', type: 'string' },
       { name: 'license', type: 'string' },
       { name: 'version', type: 'string' },
       { name: 'notation', type: 'string' },
       { name: 'type', type: 'int' },           //1 = Buy, 2 = Rent, 3 = Test
       { name: 'source', type: 'int' },
       { name: 'added', type: 'date', useNull: true },
       { name: 'creation', type: 'date', useNull: true },
       { name: 'expiration', type: 'date', useNull: true },
       { name: 'active', type: 'string' },
       { name: 'pluginId', type: 'string' }
    ]
});
//{/block}



