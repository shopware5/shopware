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
Ext.define('PaymentBillsafe.view.Detail', {
    extend: 'Ext.form.Panel',

    width: 600,
	autoScroll: true,
	bodyPadding: 5,  
	split: true,
	
    onSubmitFailure: function(form, action) {
		switch (action.failureType) {
			case Ext.form.action.Action.CLIENT_INVALID:
				Ext.Msg.alert('Fehler', 'Form fields may not be submitted with invalid values');
				break;
			case Ext.form.action.Action.CONNECT_FAILURE:
				Ext.Msg.alert('Fehler', 'Ajax communication failed');
				break;
			default:
			case Ext.form.action.Action.SERVER_INVALID:
				Ext.Msg.alert('Fehler', action.result.message);
				break;
		}
	},
    
    initComponent: function() {
    	
    	this.detailDataView = this.detailDataView.create({ detailView: this });
		this.detailPauseView = this.detailPauseView.create({ detailView: this });
		this.detailBookView = this.detailBookView.create({ detailView: this });
		this.articleListView = this.articleListView.create({ detailView: this, store: this.articleListStore });
    	
    	this.items = [{
			layout: 'column',
			border: false,
		    items: [ this.detailDataView, {
		    	width: 5,
		    	border: false,
		    	html: '&nbsp;'
		    }, {
			    columnWidth: .5,
			    border: false,
			    items: [this.detailBookView, this.detailPauseView]
			}]
		},{
			layout: 'column',
			border: false,
		    items: [ {
		    	//xtype: 'fieldset',
			    title: 'Bestellkommentar',
			    layout: 'fit',
			    columnWidth: .5,
			    height: 150,
		    	items: [{
					xtype: 'textarea',
					name: 'comment',
					border: false,
					readOnly: true,
			        enableColors: false,
			        enableAlignments: false
				}]
			}, {
		    	width: 5,
		    	border: false,
		    	html: '&nbsp;'
		    }, this.payoutListView]
		}, this.articleListView];
		
		
        this.buttons = [/*{
			text:'Teilstornierung / Teilretoure',
			handler: function (a, b, c){
				Ext.MessageBox.confirm('Teilstornierung', 'Wollen Sie wirklich eine Teilstornierung durchführen?', function(r){
					if(r!='yes') {
						return;
					}
					var form = this.getForm();
					if (!form.isValid()) {
						return;
					}
					Ext.MessageBox.wait('Bitte warten ...', 'Teilstornierung');
					
					var data = {};
					this.articleListView.store.each(function(record, i) {
						for(key in record.data) {
							data['articleList['+i+']['+key+']'] = record.data[key];
						}
					}, this);
					
					form.submit({
						params: data,
						url: '{url action=cancel}',
						success: function(form, action) {
							Ext.Msg.alert('Erfolgreich', 'Die Teilstornierung konnte erfolgreich durchgeführt werden.');
							this.loadDetail();
						},
						failure: this.onSubmitFailure,
						scope: this
					});
				}, this);
			},
			scope: this
		},*/{
			text:'Stornieren / Komplettretoure',
			handler: function (a, b, c){
				Ext.MessageBox.confirm('Stornieren', 'Wollen Sie wirklich diese Zahlung stornieren?', function(r) {
					if(r != 'yes') {
						return;
					}
					var form = this.getForm();
					if (!form.isValid()) {
						return;
					}
					Ext.MessageBox.wait('Bitte warten ...', 'Stornieren');
					form.submit({
						url: '{url action=cancel}',
						success: function(form, action) {
							Ext.Msg.alert('Erfolgreich', 'Die Zahlung konnte erfolgreich storniert werden.');
							this.loadDetail();
						},
						failure: this.onSubmitFailure,
						scope: this
					});
				}, this);
			},
			scope: this
		},{
			text: 'Teillieferung',
			handler: function (a, b, c){
				var form = this.getForm();
				if (!form.isValid()) {
					return;
				}
				Ext.MessageBox.wait('Bitte warten ...', 'Aktualisieren');
				var data = {};
				this.articleListView.store.each(function(record, i) {
					for(key in record.data) {
						data['articleList['+i+']['+key+']'] = record.data[key];
					}
				}, this);
				form.submit({
					params: data,
					url: '{url action=shipment}',
					success: function(form, action) {
						Ext.Msg.alert('Erfolgreich', 'Die Teillieferung konnte erfolgreich übermittelt werden.');
						this.loadDetail();
					},
					failure: this.onSubmitFailure,
					scope: this
				});
			},
			scope: this
		},{
			text: 'Komplettlieferung',
			handler: function (a, b, c){
				var form = this.getForm();
				if (!form.isValid()) {
					return;
				}
				Ext.MessageBox.wait('Bitte warten ...', 'Aktualisieren');

				form.submit({
					url: '{url action=shipment}',
					success: function(form, action) {
						Ext.Msg.alert('Erfolgreich', 'Die Komplettlieferung konnte erfolgreich übermittelt werden.');
						this.loadDetail();
					},
					failure: this.onSubmitFailure,
					scope: this
				});
			},
			scope: this
		}];
        
        this.callParent();
    },
    
    loadDetail: function() {
    	var id = this.getForm().getRecord().getId();
    	var store = this.listView.store;
    	store.load({
		    scope   : this,
		    callback: function(records, operation, success) {
				this.updateDetail(store.getById(id));
		    }
		});
    },

    updateDetail: function(record) {
		var form = this.getForm();
		var buttons = this.getDockedComponent(0);
		
		form.loadRecord(record);
		
		this.payoutListView.store.filters.clear();
    	this.payoutListView.store.filter('transactionId', record.get('transactionID'));
    	
    	this.articleListView.store.filters.clear();
    	this.articleListView.store.filter('transactionId', record.get('transactionID'));
    
		var maxValue = new Date();
		maxValue.setDate(maxValue.getDate() + 10);
		form.findField('pause').setMaxValue(maxValue);

		/*
		if(record.get('clear_status') == 1 || record.get('clear_status') == 2) {
			buttons.getComponent('cancelButton').show();
		} else {
			buttons.getComponent('cancelButton').hide();
		}
		
		if(record.get('clear_status') == 1 || record.get('book_amount')) {
			form.findField('book_date').show();
			form.findField('book_amount').show();
		} else {
			form.findField('book_date').hide();
			form.findField('book_amount').hide();
		}
				
		if(record.get('clear_status') == 1) {
			buttons.getComponent('bookButton').show();
			form.findField('book_date').setReadOnly(false);
			form.findField('book_amount').setReadOnly(false);
			
			form.findField('book_amount').setMaxValue(record.get('amount'));
			var maxValue = new Date(record.get('added').getTime());
			maxValue.setDate(maxValue.getDate() + 12)
			form.findField('book_date').setMaxValue(maxValue);
			form.findField('book_date').setMinValue(new Date());
		} else {
			buttons.getComponent('bookButton').hide();
			form.findField('book_date').setReadOnly(true);
			form.findField('book_amount').setReadOnly(true);
			form.findField('book_date').setMinValue(null);
		}
		
		form.findField('memo_amount').setValue(null);
		
		if(record.get('clear_status') == 2) {
			buttons.getComponent('memoButton').show();
			form.findField('memo_amount').show();
			form.findField('memo_amount').setMaxValue(record.get('book_amount'));
		} else {
			buttons.getComponent('memoButton').hide();
			form.findField('memo_amount').hide();
		}
		*/
    }
});