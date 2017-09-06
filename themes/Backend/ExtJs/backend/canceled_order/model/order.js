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
 * Shopware Model - Order model
 * Model for the 'canceled order' store
 */
//{block name="backend/canceled_order/model/order"}
Ext.define('Shopware.apps.CanceledOrder.model.Order', {
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
        //{block name="backend/canceled_order/model/order/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'orderTime', type: 'date' },
        { name: 'invoiceAmount', type: 'float' },
        { name: 'transactionId', type: 'string' },
        { name: 'cleared', type: 'int' },
        { name: 'userId', type: 'string' },
        { name: 'customerId', type: 'int' },
        { name: 'comment', type: 'string' },
        { name: 'deviceType', type: 'string' }
    ],

    /**
     * Define the associations of the list model.
     * @array
     */
    associations:[
        { type:'hasMany', model:'Shopware.apps.Base.model.Customer', name:'getCustomer', associationKey:'customer' },
        { type:'hasMany', model:'Shopware.apps.CanceledOrder.model.Position', name:'getPositions', associationKey:'details' },
        { type:'hasMany', model:'Shopware.apps.Base.model.Payment', name:'getPayment', associationKey:'payment' }
    ]
});
//{/block}
