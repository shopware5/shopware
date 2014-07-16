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
 * Shopware Plugin Manager - Update Model
 */
//{block name="backend/plugin_manager/model/update"}
Ext.define('Shopware.apps.PluginManager.model.Update', {

    /**
    * Extends the standard Ext Model
    * @string
    */
    extend: 'Ext.data.Model',

   /**
    * @array
    */
    fields: [
       //{block name="backend/plugin_manager/model/update/fields"}{/block}
       { name: 'plugin', type: 'string' },
       { name: 'name', type: 'string' },
       { name: 'currentVersion', type: 'string' },
       { name: 'availableVersion', type: 'string' },
       { name: 'pluginId', type: 'int' },
       { name: 'articleId', type: 'int' },
       { name: 'ordernumber', type: 'string' },
       { name: 'wasInstalled', type: 'int', useNull: true },
       { name: 'wasActivated', type: 'int', useNull: true },
   ]
});
//{/block}

