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
Ext.define('Shopware.apps.Customer.model.Customer', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Shopware.apps.Base.model.Customer',
    /**
     * Contains the model fields
     * @array
     */
    fields: [
        // {block name="backend/customer/model/customer/fields"}{/block}
        { name: 'newPassword', type: 'string' },
        { name: 'changed', type: 'date', dateFormat: 'c' },
        { name: 'amount', type: 'float' },
        { name: 'orderCount', type: 'int' },
        { name: 'failedLogins', type: 'int' },
        { name: 'canceledOrderAmount', type: 'float' },
        { name: 'shopName', type: 'string' },
        { name: 'language', type: 'string' },
        { name: 'birthday', type: 'date', dateFormat: 'd.m.Y' },
        { name: 'title', type: 'string' },
        { name: 'salutation', type: 'string' },
        { name: 'firstname', type: 'string' },
        { name: 'lastname', type: 'string' },
        { name: 'title', type: 'string' },
        { name: 'number', type: 'string' },
        { name: 'serverTime', type: 'date', defaultValue: null },
        {
            name: 'lockedUntil',
            type: 'date',
            defaultValue: null,
            convert: function (value, record) {
                if (record.get('serverTime') === null || value === null || !Ext.isDefined(record.get('serverTime'))) {
                    return value;
                }

                if (record.get('serverTime').getTime() >= value.getTime()) {
                    return null;
                }

                return value;
            }
        },
        { name: 'customerStreamIds', type: 'string' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        /**
         * Set proxy type to ajax
         * @string
         */
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read: '{url action="getDetail"}',
            update: '{url action="save"}',
            create: '{url action="save"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }

    },

    /**
     * Define the associations of the customer model.
     * One customer has a billing, shipping address and a debit information.
     * @array
     */
    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Customer.model.Address', name: 'getDefaultBillingAddress', associationKey: 'defaultBillingAddress' },
        { type: 'hasMany', model: 'Shopware.apps.Customer.model.Address', name: 'getDefaultShippingAddress', associationKey: 'defaultShippingAddress' },
        { type: 'hasMany', model: 'Shopware.apps.Customer.model.Debit', name: 'getDebit', associationKey: 'debit' },
        { type: 'hasMany', model: 'Shopware.apps.Customer.model.PaymentData', name: 'getPaymentData', associationKey: 'paymentData' }
    ]

});
// {/block}
