/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Shopware_Paypal
 * @subpackage Paypal
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

/**
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.PaymentPaypal.model.main.Detail', {
    extend: 'Ext.data.Model',
	fields: [
        { name: 'transactionId', type: 'string' },
        //{ name: 'orderNumber', type: 'string' },

		{ name: 'addressStatus',  type: 'string' },
        { name: 'addressName',  type: 'string' },
        { name: 'addressStreet',  type: 'string' },
        { name: 'addressCity',  type: 'string' },
        { name: 'addressCountry',  type: 'string' },
        { name: 'addressPhone',  type: 'string' },

        { name: 'accountEmail',  type: 'string' },
        { name: 'accountName',  type: 'string' },
        { name: 'accountStatus',  type: 'string' },

        { name: 'protectionStatus',  type: 'string' },
        { name: 'paymentStatus',  type: 'string' },
        { name: 'pendingReason',  type: 'string' },

        { name: 'paymentDate',  type: 'date' },
        { name: 'paymentType',  type: 'string' },
        { name: 'paymentAmount',  type: 'string' },
        { name: 'paymentCurrency',  type: 'string' },
        { name: 'paymentAmountFormat', type: 'string' }
	],

    associations: [{
        type: 'hasMany', model: 'Shopware.apps.PaymentPaypal.model.main.Transaction',
        name: 'getTransactions', associationKey: 'transactions'
    }]
});