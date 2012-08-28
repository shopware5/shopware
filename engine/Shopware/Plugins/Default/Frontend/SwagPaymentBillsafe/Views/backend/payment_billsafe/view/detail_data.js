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
Ext.define('PaymentBillsafe.view.DetailData', {
    extend: 'Ext.form.FieldSet',
    layout: 'anchor',
    xtype: 'fieldset',
    title: 'Bestelldaten',
	border: false,
    columnWidth: .5,
    margin: 0,
    defaults: {
        anchor: '100%',
        readOnly: true,
        xtype: 'textfield'
    },
    items: [{
        xtype: 'hiddenfield',
        name: 'payment_key'
    },{
        xtype: 'hiddenfield',
        name: 'currency'
    },{
        xtype: 'hiddenfield',
        name: 'dispatch_description'
    },{
        xtype: 'hiddenfield',
        name: 'trackingID'
    },{
        xtype: 'hiddenfield',
        name: 'invoice_number'
    },{
        xtype: 'datefield',
        fieldLabel: 'Bestelldatum',
        name: 'order_date'
    },{
		fieldLabel: 'Transaktions-ID',
		name: 'transactionID'
	},{
		fieldLabel: 'Bestellnummer',
		name: 'order_number'
	},{
		fieldLabel: 'Kunde',
		name: 'customer'
	},{
		xtype: 'numberfield',
		decimalPrecision: 2,
        fieldLabel: 'Betrag',
        name: 'amount'
    }/*,{
		fieldLabel: 'Zahlstatus',
		name: 'customer'
	}*/]
});