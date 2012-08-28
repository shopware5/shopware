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
Ext.define('Shopware.apps.PaymentPaypal.model.main.List', {
	extend: 'Ext.data.Model',
	fields: [
		{ name: 'id', type: 'int' },
		{ name: 'userId',  type: 'string' },
		{ name: 'transactionId', type: 'string' },
		
		{ name: 'clearedId', type: 'int' },
		{ name: 'statusId', type: 'int' },
		{ name: 'clearedDescription', type: 'string' },
		{ name: 'statusDescription', type: 'string' },
		
		{ name: 'currency', type: 'string' },
		{ name: 'amount', type: 'float' },
		{ name: 'amountFormat', type: 'string' },
		{ name: 'customer', type: 'string' },
        { name: 'customerId', type: 'string' },
		{ name: 'orderDate', type: 'date' },
		{ name: 'clearedDate', type: 'date' },
		{ name: 'orderNumber', type: 'string' },
		{ name: 'paymentDescription', type: 'string' },
		{ name: 'paymentKey', type: 'string' },
		{ name: 'comment', type: 'string' },
		
		{ name: 'invoiceNumber', type: 'string' },
		{ name: 'invoiceHash', type: 'string' },
		{ name: 'trackingId', type: 'string' },
		{ name: 'dispatchId', type: 'int' },
		{ name: 'dispatchDescription', type: 'string' }
	]
});