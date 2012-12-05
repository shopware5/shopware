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
Ext.define('PaymentBillsafe.view.List', {

	extend: 'Ext.grid.Panel',
	
	title: 'Zahlungen',
	layout: 'fit',
	viewConfig: {
        stripeRows: true
    },
    
    columns: [{
        text     : 'Datum',
        width    : 85,
        sortable : true,
        xtype    : 'datecolumn',
        dataIndex: 'order_date'
    },{
        text     : 'Bestellnummer',
        width    : 85,
        sortable : true,
        dataIndex: 'order_number'
    },{
        text     : 'Transaktions-ID',
        width     : 85,
        sortable : true,
        dataIndex: 'transactionID'
    },{
        text     : 'Zahlungsart',
        width    : 85,
        sortable : true,
        dataIndex: 'payment_description'
    },{
        text     : 'Kunde',
        width    : 120,
        sortable : true,
        dataIndex: 'customer'
    },{
        text     : 'Betrag',
        width    : 85,
        sortable : true,
        align    : 'right',
        renderer : function(value, column, model) {
        	return model.data.amount_format;
        },
        dataIndex: 'amount'
    },{
        text     : 'Bestellstatus',
        width    : 100,
        sortable : true,
        dataIndex: 'statusID',
        renderer : function(value, column, model) {
        	return model.data.status_description;
        }
    },{
        text     : 'Zahlstatus',
        width    : 100,
        sortable : true,
        dataIndex: 'cleardID',
        renderer : function(value, column, model) {
        	return model.data.cleared_description;
        }
    },{
        xtype:'actioncolumn', 
        width:60,
        items: [{
           
            iconCls: 'sprite-user--pencil',
            tooltip: 'Kundenkonto öffnen',
            handler: function(grid, rowIndex, colIndex) {
                var record = grid.getStore().getAt(rowIndex);
                parent.parent.parent.Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Customer',
                    action: 'detail',
                    params: {
                        customerId: record.get("userID")
                    }
                });
            }
        }, {
            iconCls: 'sprite-receipt-sticky-note',
            tooltip: 'Bestellung öffnen',
            handler: function(grid, rowIndex, colIndex) {
            	 var record = grid.getStore().getAt(rowIndex);
                parent.parent.parent.Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Order',
                    params: {
                        orderId:record.get('id')
                    }
                });
            }
        }, {
            iconCls: 'sprite-receipt-invoice',
            tooltip: 'Rechnung öffnen',
            getClass: function(value, metadata, record) {
            	if(!record.get('invoice_number')) {
            		return 'action_hidden';
            	}
            },
            handler: function(grid, rowIndex, colIndex) {
            	var record = grid.getStore().getAt(rowIndex);
                //parent.loadSkeleton('orders', false, { 'id': record.get('id') });
                window.open("{link file='engine/backend/modules/orders/openPDF.php'}?pdf=" + record.get('invoice_hash'), '_blank');
            }
        }]
    }],
    
    //invoice_number
    
    initComponent: function() {
    	
    	this.onTextFieldChange = function() {
    		var value = this.searchField.getValue();
    		this.store.filters.clear();
    		this.store.filter('search', value);
    	};
    	
    	this.searchField = Ext.create('Ext.form.field.Text', {
             xtype: 'textfield',
             name: 'searchField',
             hideLabel: true,
             width: 200,
             listeners: {
                 change: {
                     fn: this.onTextFieldChange,
                     scope: this,
                     buffer: 100
                 }
             }
        });
    	
    	this.dockedItems = [{
	        xtype: 'pagingtoolbar',
	        store: this.store,
	        dock: 'bottom',
	        displayInfo: true,
	        items: [
	        	'Suche: ', this.searchField
	        ]
	    }];
    	
        this.callParent();
    }
});