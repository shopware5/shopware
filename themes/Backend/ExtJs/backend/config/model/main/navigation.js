/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    Shopware_Config
 * @subpackage Config
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/config/model/main/navigation"}
Ext.define('Shopware.apps.Config.model.main.Navigation', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/config/model/main/navigation/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'text', convert: function(v, record) { return record.data.label; } },
        { name: 'leaf', convert: function(v, record) { return record.data.childrenCount <= 0; } },
        { name: 'label', type: 'string' },
        { name: 'childrenCount', type: 'int' }

        //{ name: 'loaded', type: 'boolean', defaultValue: false },
        //{ name: 'action' },
        //{ name: 'expanded', defaultValue: true },
        //{ name: 'children' }
    ]
});
//{/block}
