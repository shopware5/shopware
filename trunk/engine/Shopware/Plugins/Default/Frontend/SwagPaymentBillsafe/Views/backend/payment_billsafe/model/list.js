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
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

/**
 * todo@all: Documentation
 */
Ext.define('PaymentBillsafe.model.List', {
	extend: 'Ext.data.Model',
	fields: [
		{ name: 'id', type: 'int' },
		{ name: 'userID',  type: 'string' },
		{ name: 'transactionID', type: 'string' },
		
		{ name: 'clearedID', type: 'int' },
		{ name: 'statusID', type: 'int' },
		{ name: 'cleared_description', type: 'string' },
		{ name: 'status_description', type: 'string' },
		
		{ name: 'currency', type: 'string' },
		{ name: 'amount', type: 'float' },
		{ name: 'amount_format', type: 'string' },
		{ name: 'customer', type: 'string' },
		{ name: 'order_date', type: 'date', dateFormat: 'c'},
		{ name: 'cleared_date', type: 'date', dateFormat: 'c'},
		{ name: 'order_number', type: 'string' },
		{ name: 'payment_description', type: 'string' },
		{ name: 'payment_key', type: 'string' },
		{ name: 'comment', type: 'string' },
		
		{ name: 'invoice_number', type: 'string' },
		{ name: 'invoice_hash', type: 'string' },
		{ name: 'trackingID', type: 'string' },
		{ name: 'dispatchID', type: 'int' },
		{ name: 'dispatch_description', type: 'string' }
	]
});