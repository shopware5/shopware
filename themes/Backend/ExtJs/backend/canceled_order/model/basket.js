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
 * @package    CanceledOrder
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Basket model
 * Model for the 'canceled baskets' store
 */
//{block name="backend/canceled_order/model/basket"}
Ext.define('Shopware.apps.CanceledOrder.model.Basket', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/canceled_order/model/basket/fields"}{/block}
        { name: 'date' },
        { name: 'price', type: 'int' },
        { name: 'average', type: 'int' },
        { name: 'year', type: 'string' },
        { name: 'month', type: 'string' },
        {
            name: 'groupDate',
            type: 'date',
            // Convert date to groupDate for the grouping grid
            convert: function(value, record) {
                if (record && record.get('year') && record.get('month')) {
                    return new Date(record.get('year'), record.get('month') - 1);
                }
            }
        },
        { name: 'average', type: 'float' },
        { name: 'number', type: 'int' },
    ]
});
//{/block}
