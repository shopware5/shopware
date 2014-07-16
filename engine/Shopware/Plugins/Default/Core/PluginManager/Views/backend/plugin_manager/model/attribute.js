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
 * Shopware Plugin Manager - Attribute Model
 * The attribute model contains some additional configuration for the community product.
 * For example the licence_key.
 */
//{block name="backend/plugin_manager/model/attribute"}
Ext.define('Shopware.apps.PluginManager.model.Attribute', {

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
        //{block name="backend/plugin_manager/model/attribute/fields"}{/block}
        { name: 'changelog', type: 'string', useNull: true },
        { name: 'licence_key', type: 'string', useNull: true },
        { name: 'test_modus', type: 'int', useNull: true },
        { name: 'version', type: 'string', useNull: true },
        { name: 'certificate', type: 'int', useNull: true },
        { name: 'support_by', type: 'int', useNull: true },
        { name: 'forum', type: 'string', useNull: true },
        { name: 'install_description', type: 'string', useNull: true },
        { name: 'shopware_compatible', type: 'string', useNull: true },
        { name: 'forum_url', type: 'string', useNull: true },
        { name: 'store_url', type: 'string', useNull: true }

    ]
});
//{/block}

