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
Ext.define('PaymentBillsafe.view.PayoutList', {

	extend: 'Ext.grid.Panel',

	title: 'Auszahlungen / Retouren',
	height: 150,
	//margin: '8 0 0 0',
	columnWidth: .5,
	viewConfig: {
        stripeRows: true
    },

    columns: [{
        text     : 'Datum',
        width    : 100,
        sortable : true,
        xtype    : 'datecolumn',
        dataIndex: 'date'
    },{
        text     : 'Nummer',
        width    : 100,
        sortable : true,
        dataIndex: 'number'
    },{
        text     : 'Betrag',
        width    : 80,
        sortable : true,
        align    : 'right',
        renderer : function(value, column, model) {
        	if(!model.data.amount_format) {
        		return 'retour';
        	}
        	return model.data.amount_format;
        },
        dataIndex: 'amount'
    }]
});