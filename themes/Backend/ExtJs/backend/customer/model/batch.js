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
 * @package    Customer
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}

/**
 * Shopware Model - Customer list backend module.
 *
 * The customer model of the customer module represent a data row of the s_user or the
 * Shopware\Models\Customer\Customer doctrine model, with some additional data for the additional information panel.
 */
// {block name="backend/customer/model/customer"}
Ext.define('Shopware.apps.Customer.model.Batch', {

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
        // {block name="backend/customer/model/customer/fields"}{/block}
        { name: 'id', type: 'int' }
    ],

    /**
     * Define the associations of the customer model.
     * One customer has a billing, shipping address and a debit information.
     * @array
     */
    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Base.model.OrderStatus', name: 'getOrderStatus', associationKey: 'orderStatus' },
        { type: 'hasMany', model: 'Shopware.apps.Base.model.PaymentStatus', name: 'getPaymentStatus', associationKey: 'paymentStatus' },
        { type: 'hasMany', model: 'Shopware.apps.Base.model.Payment', name: 'getPayment', associationKey: 'payment' },
        { type: 'hasMany', model: 'Shopware.apps.Base.model.Dispatch', name: 'getDispatch', associationKey: 'dispatch' },
        { type: 'hasMany', model: 'Shopware.apps.Base.model.Shop', name: 'getShop', associationKey: 'shop' },
        { type: 'hasMany', model: 'Shopware.apps.Base.model.Country', name: 'getCountry', associationKey: 'country' },
        { type: 'hasMany', model: 'Shopware.apps.Base.model.CustomerGroup', name: 'getCustomerGroup', associationKey: 'customerGroup' }
    ]

});
// {/block}
