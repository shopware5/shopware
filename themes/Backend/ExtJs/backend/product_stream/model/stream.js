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
 * @package    ProductStream
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */
//{block name="backend/product_stream/model/stream"}
Ext.define('Shopware.apps.ProductStream.model.Stream', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'ProductStream'
        };
    },

    fields: [
        //{block name="backend/product_stream/model/stream/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string', useNull: false },
        { name: 'description', type: 'string', useNull: false },
        { name: 'type', type: 'int', defaultValue: 1 },
        { name: 'sorting' },
        { name: 'sortingId', type: 'int', useNull: true },
        { name: 'conditions', useNull: false }
    ]
});
//{/block}
