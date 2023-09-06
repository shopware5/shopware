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
 * @package    Order
 * @subpackage Model
 * @version    $Id$
 * @author     shopware AG
 */

/**
 * Shopware Model - Order list backend module.
 */
//{block name="backend/order/model/payment_instance"}
Ext.define('Shopware.apps.Order.model.PaymentInstance', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Shopware.data.Model',

    idProperty: 'id',

    /**
     * One or more BelongsTo associations for this model.
     * @string
     */
    belongsTo: 'Shopware.apps.Order.model.Order',

    fields: [
        //{block name="backend/order/model/payment_instance/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'firstname', type: 'string' },
        { name: 'lastname', type: 'string' },
        { name: 'address', type: 'string' },
        { name: 'zipcode', type: 'string' },
        { name: 'city', type: 'string' },
        { name: 'accountNumber', type: 'string' },
        { name: 'accountHolder', type: 'string' },
        { name: 'bankName', type: 'string' },
        { name: 'bankCode', type: 'string' },
        { name: 'bic', type: 'string' },
        { name: 'iban', type: 'string' },
        { name: 'amount', type: 'string' }
    ]
});
//{/block}
