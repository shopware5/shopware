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
 * @package    Performance
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Basic key value model
 */
//{block name="backend/performance/model/key_value"}
Ext.define('Shopware.apps.Performance.model.KeyValue', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * Contains the model fields
     * @array
     */
    fields: [
        //{block name="backend/performance/model/key_value/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'key', type: 'string' },
        { name: 'value', type: 'string' }
    ]

});
//{/block}
