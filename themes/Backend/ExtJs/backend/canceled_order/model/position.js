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
 */

/**
 * Shopware Model - CanceledOrder position list backend module.
 *
 * The list model represents a single row for the order list grid.
 */
//{block name="backend/canceled_order/model/position"}
Ext.define('Shopware.apps.CanceledOrder.model.Position', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Ext.data.Model',

    /**
     * Unique identifier field
     * @string
     */
    idProperty:'id',

    /**
     * The fields used for this model
     * @array
     */
    fields:[
        //{block name="backend/canceled_order/model/position/fields"}{/block}
        { name: 'id', type:'int' },
        { name: 'orderId', type:'int' },
        { name: 'mode', type:'int' },
        { name: 'articleId', type:'int' },
        { name: 'articleNumber', type:'string' },
        { name: 'articleName', type:'string' },
        { name: 'quantity', type:'int' },
        { name: 'statusId', type:'int' },
        { name: 'statusDescription', type:'string' },
        { name: 'price', type:'float' },
        { name: 'taxId', type:'int' },
        { name: 'taxRate', type:'float' },
        { name: 'taxDescription', type:'string' },
        { name: 'inStock', type:'int' },
        {
            name: 'total',
            type:'float',
            convert: function(value, record) {
                if (!Ext.isNumeric(record.get('price'))) {
                    return record.get('price');
                }
                return record.get('price') * record.get('quantity');
            }
        }
    ]
});
//{/block}
