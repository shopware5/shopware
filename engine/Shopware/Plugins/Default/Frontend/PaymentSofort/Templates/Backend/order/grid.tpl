<script type="text/javascript">
<!--
/**
 * ExtJS Grid to display orders in Shopware's backend
 *
 * $Date: 2012-08-21 16:28:24 +0200 (Di, 21. Aug 2012) $
 * @version sofort 1.0  $Id: grid.tpl 5178 2012-08-21 14:28:24Z dehn $
 * @author Payment Network AG http://www.payment-network.com (f.dehn@sofort.com)
 * @package Shopware 4.x, sofort.com
 *
 */

Ext.ns('sofort.Extjs');

sofortCookieStack = new sofortCookieStack('sofortGridStore', 60);
sofortCookieStack.clearCookie('sofortGridStore');

var storePage = 0;
var storeLimit = 20;
Ext.QuickTips.init();

Ext.grid.RowSelectionModel.override ({
	getSelectedIndex : function(){
		return this.grid.store.indexOf(this.selections.itemAt(0));
	}
});

var loadingMask = new Ext.LoadMask(Ext.getBody());
loadingMask.msg = "{s name='please_wait' namespace='sofort_multipay_backend'}{/s}";

sofortStore = new Ext.data.Store({
	url: "{url action=getSofortOrders}",
	start : storePage,
	limit : storeLimit,
	remoteSort: true,
	autoLoad: true,
	reader: new Ext.data.JsonReader({
		root: 'data',
		totalProperty: 'count',
		id: 'id',
		fields: [
			'ordernumber', 'details', 'userId', 'dateTime', 'dateModified', 'paymentDescription', 'customerProtection', 'paymentStatus',
			'transactionId', 'amount', 'comment', 'internal_comment',
			'actions', 'cleared', 'cleared_description', 'paymentMethod', 'dateModifiedTimestamp'
		]
	}),
	listeners: {
		'beforeload' : function(store, records, options) {
			loadingMask.show();
		},
		'load' : function(store, records, options) {
			loadingMask.hide();
		}
	}
});


Ext.util.Format.deMoney = function(v) {
	v = (Math.round((v-0)*100))/100;
	v = (v == Math.floor(v)) ? v + ".00" : ((v*10 == Math.floor(v*10)) ? v + "0" : v);
	return (v + ' &euro;');
};


Ext.util.Format.commentRenderer = function(comment) {
	return '<span class="sofort_history_comment">' + comment + '</span>';
}


Ext.util.Format.taxValue = function(v) {
	return (v + ' %');
};


var expander = new Ext.grid.SofortRowExpander({
	tpl : '<div class="ux-row-expander-box"></div>',
	actAsTree : true,
	treeLeafProperty : 'is_leaf',
	listeners: {
		expand : function (expander, record, body, rowIndex) {
			makeLayoutPanel(expander, record, body, rowIndex);
		}
	}
});


var makeLayoutPanel = function(expander, record, body, rowIndex) {
	var parentElement = Ext.get(this.grid.getView().getRow(rowIndex)).child( '.ux-row-expander-box');
	var orderNumber = sofortStore.data.get(rowIndex).data.ordernumber;
	setBorderPanel(orderNumber, rowIndex, parentElement);
};


var setBorderPanel = function(orderNumber, rowIndex, parentElement) {
	var status = '';
	var box = Ext.MessageBox.wait('{s name="please_wait" namespace="sofort_multipay_backend"}{/s}', '{s name="please_wait" namespace="sofort_multipay_backend"}{/s}');
	Ext.Ajax.request({
		url: '{url action=getOrderStatus}',
		params: 'orderId=' + orderNumber,
		failure:function(response,options){
			Ext.MessageBox.alert('Warning','{s name="admin.action.warning" namespace="sofort_multipay_backend"}{/s}');
			box.hide();
		},
		success: function(response){
			var jsonResponse = Ext.util.JSON.decode(response.responseText);
			var dateModified = jsonResponse[0].dateModifiedTimestamp;
			var status = jsonResponse[0].status_reason;
			var myBorderPanel = new Ext.Panel({
				renderTo: this.parentElement,
				items: [
					new DetailGridPanel(rowIndex, parentElement, status, dateModified),
					new HistoryGridPanel(rowIndex, parentElement),
				]
			});
			box.hide();
		}
	});
};


var filters = new Ext.ux.grid.GridFilters({
	filters:[
		{
			type: 'list',
			dataIndex: 'paymentDescription',
			options: [
				'{s name="sofort_multipay_su_public_title" namespace="sofort_multipay_bootstrap"}{/s}',
				'{s name="sofort_multipay_sl_public_title" namespace="sofort_multipay_bootstrap"}{/s}',
				'{s name="sofort_multipay_ls_public_title" namespace="sofort_multipay_bootstrap"}{/s}',
				'{s name="sofort_multipay_sr_public_title" namespace="sofort_multipay_bootstrap"}{/s}',
				'{s name="sofort_multipay_sv_public_title" namespace="sofort_multipay_bootstrap"}{/s}'],
			phpMode: true
		},
		{
			type: 'list',
			dataIndex: 'cleared_description',
			options: [
				'{s name="payment_state_open" namespace="sofort_multipay_bootstrap"}{/s}',
				'{s name="payment_state_confirmed" namespace="sofort_multipay_bootstrap"}{/s}',
				'{s name="payment_state_canceled" namespace="sofort_multipay_bootstrap"}{/s}'
				//'{s name="payment_state_refunded" namespace="sofort_multipay_bootstrap"}{/s}'
			],
			phpMode: true
		}
	]
});


sofortGrid = Ext.extend(Ext.grid.EditorGridPanel, {
	id:'PaymentNetwork',
	cls: 'sofortGrid',
	title: '{s name="orders" namespace="sofort_multipay_backend"}{/s}',
	stripeRows: true,
	autoSizeColumns: true,
	region: 'center',
	clicksToEdit: 2,
	plugins: [expander, filters],
	cm : new Ext.grid.ColumnModel([
		expander,
		{
			xtype: 'gridcolumn',
			dataIndex: 'dateTime',
			type: 'date',
			header: '{s name="date" namespace="sofort_multipay_backend"}{/s}',
			sortable: true
		},
		{
			xtype: 'gridcolumn',
			dataIndex: 'dateModified',
			type: 'date',
			header: '{s name="date_last_update" namespace="sofort_multipay_bootstrap"}{/s}',
			sortable: true
		},
		{
			xtype: 'gridcolumn',
			dataIndex: 'userId',
			header: '{s name="customer_number" namespace="sofort_multipay_backend"}{/s}',
			sortable: true
		},
		{
			xtype: 'gridcolumn',
			dataIndex: 'ordernumber',
			header: '{s name="order_number" namespace="sofort_multipay_backend"}{/s}',
			editor: new Ext.form.TextField({
				allowBlank: false
			}),
			sortable: true
		},
		{
			xtype: 'gridcolumn',
			dataIndex: 'transactionId',
			header: '{s name="sofort_multipay_transaction_id" namespace="sofort_multipay_finish"}{/s}',
			sortable: true,
			editable: true,
			readOnly: false,
			editor: new Ext.form.TextField({
				allowBlank: false
			})
		},
		{
			xtype: 'gridcolumn',
			id: 'cleared_description',
			header: '{s name="payment_status" namespace="sofort_multipay_backend"}{/s}',
			dataIndex: 'cleared_description',
			sortable: true
		},
		{
			xtype: 'gridcolumn',
			dataIndex: 'paymentDescription',
			header: '{s name="payment_method" namespace="sofort_multipay_backend"}{/s}',
			sortable: true
		},
		{
			xtype: 'gridcolumn',
			id: 'amount',
			header: '{s name="total" namespace="sofort_multipay_backend"}{/s}',
			dataIndex: 'amount',
			sortable: true,
			renderer : Ext.util.Format.deMoney
		},
	]),
	viewConfig: {
		forceFit: true
	},
	initStore: function() {
		this.store = sofortStore;
		this.store.setDefaultSort('dateTime', 'desc');
		this.bbar = this.pagingBar(this.store);
		this.store.load();
		this.tbar = this.toolBar(this.store);
	},
	initComponent: function() {
		var store = this.initStore();
		sofortGrid.superclass.initComponent.call(this);
		return store;
	},
	pagingBar: function(store) {
		bar = new Ext.PagingToolbar({
			pageSize: storeLimit,
			store: sofortStore,
			displayInfo: true,
			emptyMsg: '{s name="no_transaction_found" namespace="sofort_multipay_backend"}{/s}'
		});
		return bar;
	},
	toolBar: function(store) {
		toolbar = new Ext.Toolbar({
			renderTo: document.body,
			store: store,
			items: [
					textField = new Ext.form.TextField({
						name: 'name',
						id : 'word-search',
						enableKeyEvents: true,
						emptyText: '{s name="search_for" namespace="sofort_multipay_backend"}{/s}'
					}),
				{
					xtype: 'button',
					text : '{s name="search" namespace="sofort_multipay_backend"}{/s}',
					handler : function() {
						searchSomething(Ext.get('word-search').dom.value);	// start the search on pressing the button
					}
				},
				{	// sofort.com logo
					xtype: 'box',
					autoEl: {
						html: '<div style="position: absolute; right: 2px; top: 4px" ><img height="20px" src="https://images.sofort.com/de/sofort/sofort_logo125px.gif" /></div>'
					}
				}
			]
		});
		textField.on('keydown', function(element, event) {
			if(event.keyCode == event.ENTER) {
				searchSomething(Ext.get('word-search').dom.value);	// start the search by pressing the button
			}
		});
		return toolbar;
	}
});


var DetailGridPanel = function (id, element, paymentStatus, dateModified) {
	var index = id;
	var transactionId = sofortStore.data.get(id).data.transactionId;
	var transactionEntity = sofortCookieStack.getValue(transactionId);
	
	if(transactionEntity === undefined || transactionEntity.time <= dateModified) {
		var paymentStatus = paymentStatus;
	} else if(transactionEntity !== undefined && transactionEntity.time > dateModified) {
		var paymentStatus = transactionEntity.status;
	}
	
	var paymentMethod = sofortStore.data.get(id).data.paymentMethod;
	var orderNumber = sofortStore.data.get(id).data.ordernumber;
	var customerProtection = sofortStore.data.get(id).data.customerProtection;
	
	var transactionFunctions = function(paymentStatus, customerProtection) {
		var placeholder = { 
			xtype: 'label', 
			html: '<div style="height: 20px">&nbsp;</div>',
			name: 'label_name', 
			height: 180
		};
		
		var confirmInvoiceButton = {
				xtype: 'button',
				id: 'confirm_invoice',
				cls: 'confirm_invoice',
				tooltip:'{s name="admin.action.confirm_invoice" namespace="sofort_multipay_backend"}{/s}',
				itemCls: 'accept_circle',
				text: '{s name="admin.action.confirm_invoice" namespace="sofort_multipay_backend"}{/s}',
				handler : function(){
					confirmInvoice(transactionId, paymentStatus);
				}
		};
		
		
		var cancelInvoiceString = '';
		
		if(paymentStatus == 'confirm_invoice') {
			cancelInvoiceString = '{s name="admin.action.cancel_invoice" namespace="sofort_multipay_backend"}{/s}';
		} else if(paymentStatus == 'not_credited_yet') {
			cancelInvoiceString = '{s name="admin.action.cancel_confirmed_invoice" namespace="sofort_multipay_backend"}{/s}';
		}
		
		var cancelInvoiceButton = {
			xtype: 'button',
			id: 'cancel_invoice',
			cls: 'cancel_invoice',
			//tooltip:'{s name="admin.action.cancel_invoice" namespace="sofort_multipay_backend"}{/s}',
			//text: '{s name="admin.action.cancel_invoice" namespace="sofort_multipay_backend"}{/s}',
			text: cancelInvoiceString,
			handler : function(){
				cancelInvoice(transactionId, paymentStatus);
			}
		};
		
		var invoiceDownloadString = '';
		
		if(paymentStatus == 'confirm_invoice') {
			invoiceDownloadString = '{s name="admin.action.download_invoice_preview" namespace="sofort_multipay_backend"}{/s}';
		} else if(paymentStatus == 'not_credited_yet') {
			invoiceDownloadString = '{s name="admin.action.download_invoice" namespace="sofort_multipay_backend"}{/s}';
		} else if(paymentStatus == 'refunded') {
			invoiceDownloadString = '{s name="admin.action.download_credit_memo" namespace="sofort_multipay_backend"}{/s}';
		}
		
		
		var printInvoiceButton = {
			xtype: 'button',
			id: 'print_invoice',
			cls: 'print_invoice',
			tooltip: '{s name="admin.action.download_invoice.hint" namespace="sofort_multipay_backend"}{/s}',
			text: invoiceDownloadString,
			handler : function(){
				printInvoice(transactionId, paymentStatus);
			}
		};
		
		
		var makeLogoLabel = function(paymentMethod, customerProtection) {
			if(customerProtection == 1) {
				var customerProtectionText = '<div style="position: absolute; right: 92px; top: 7px">{s name="admin.customerprotection_activated" namespace="sofort_multipay_backend"}{/s}</div>';
				var customerProtectionLogo = '<div style="position: absolute; right: 68px; top: 3px"><img height="20px" src="{s name="admin.cp_image" namespace="sofort_multipay_backend"}{/s}" /></div>';
			} else {
				customerProtection = '';
				customerProtectionText = '';
				customerProtectionLogo = '';
			}
			
			var paymentMethodLogo = customerProtectionText + customerProtectionLogo + '<div style="position: absolute; right: 16px; top: 4px" ><img height="20px" src="https://images.sofort.com/de/'+paymentMethod+'/logo_90x30.png" /></div>';
			
			return {
				xtype: 'box',
				autoEl: {
					html: paymentMethodLogo
				}
			};
		};
		
		var elements = [];
		
		switch(paymentMethod) {
			case 'sofortrechnung_multipay' :
				switch(paymentStatus) {
					case 'confirm_invoice':
						var elements = [
							confirmInvoiceButton,
							cancelInvoiceButton,
							printInvoiceButton
						];
					break;
					case 'not_credited_yet':
						var elements = [
							cancelInvoiceButton,
							printInvoiceButton
						];
					break;
					case 'refunded':
						var elements = [
							printInvoiceButton
						];
					break;
				}
				elements.push(makeLogoLabel('sr'));
			break;
			case 'sofortlastschrift_multipay' :
				var elements = [
					placeholder,
					makeLogoLabel('sl')
				];
			break;
			case 'vorkassebysofort_multipay' :
				var elements = [
					placeholder,
					makeLogoLabel('sv', customerProtection)
				];
			break;
			case 'sofortueberweisung_multipay' :
				var elements = [
					placeholder,
					makeLogoLabel('su', customerProtection)
				];
			break;
			case 'lastschriftbysofort_multipay' :
				var elements = [
					placeholder,
					makeLogoLabel('ls')
				];
			break;
		}
		
		return elements;
	};
	
	
	var cartFunctions = function(paymentStatus) {
		var elements = [
			{
				xtype: 'button',
				tooltip:'{s name="admin.action.update_cart.hint" namespace="sofort_multipay_backend"}{/s}',
				text: '{s name="admin.action.update_cart" namespace="sofort_multipay_backend"}{/s}',
				handler : function(e){
					//var paymentStatus = sofortStore.data.get(id).data.paymentStatus;
					updateCart(transactionId, paymentStatus, orderDetailStore, id);
				}
			}
		];
		if(paymentMethod == 'sofortrechnung_multipay' && paymentStatus != 'refunded' && paymentStatus != 'canceled') {
			return elements;
		}
		else return null;
	};
	
	
	var orderDetailStore = new Ext.data.Store({
		url: "{url action=getOrderDetails}",
		baseParams: {
			orderId: sofortStore.data.get(id).data.ordernumber,
			transactionId: sofortStore.data.get(id).data.transactionId
		},
		autoLoad: true,
		reader: new Ext.data.JsonReader({
			totalProperty: 'count',
			id: 'id',
			fields: [
				'name', 'description', 'articleordernumber', 'price', 'netPrice', 'quantity', 'sum', 'transactionId', 'tax', 'articleId', 'delete', 'productType'
			]
		}),
		listeners: {
			'beforeload' : function(store, records, options) {
				loadingMask.show();
			},
			'load' : function(store, records, options) {
				loadingMask.hide();
			}
		}
	});
	
	
	var checkColumn =  new Ext.ux.grid.CheckColumn({
		header: '{s name="admin.action.remove_from_invoice" namespace="sofort_multipay_backend"}{/s}',
		tooltip: '{s name="admin.action.remove_from_invoice.hint" namespace="sofort_multipay_backend"}{/s}',
		id: "checkColumn",
		dataIndex: 'delete',
		sortable: false,
		paymentMethod: paymentMethod,
		paymentStatus: paymentStatus,
		width: 200
	});
	
	
	var editableQuantityColumn = function(){
		var isEditable = false;
		
		tooltipMessage = '';
		if (paymentMethod == 'sofortrechnung_multipay' && paymentStatus != 'refunded' && paymentStatus != 'canceled') {
			isEditable = true;
			tooltipMessage = '{s name="admin.action.update_quantity" namespace="sofort_multipay_backend"}{/s}';
		}
		var column = {	
			xtype: 'gridcolumn',
			dataIndex: 'quantity',
			header: '{s name="admin.action.quantity" namespace="sofort_multipay_backend"}{/s}',
			sortable: true,
			id: 'quantityColumn',
			tooltip: tooltipMessage,
			editable: isEditable,
			readOnly: false,
			editor: new Ext.form.TextField({
				allowBlank: false
			}),
			width: 150
		};
		return column;
	};
	
	
	var editableAmountColumn = function(){
		var isEditable = false;
		tooltipMessage = '';
		if (paymentMethod == 'sofortrechnung_multipay' && paymentStatus != 'refunded' && paymentStatus != 'canceled') {
			isEditable = true;
			tooltipMessage = '{s name="admin.action.update_price" namespace="sofort_multipay_backend"}{/s}';
		}
		var column = {	
			xtype: 'gridcolumn',
			dataIndex: 'price',
			header: '{s name="admin.action.price" namespace="sofort_multipay_backend"}{/s}',
			sortable: true,
			id: 'amountColumn',
			tooltip: tooltipMessage,
			editable: isEditable,
			readOnly: false,
			editor: new Ext.form.TextField({
				allowBlank: false
			}),
			width: 150,
			renderer: Ext.util.Format.deMoney
		};
		return column;
	};
	
	
	var detailGrid = new Ext.grid.EditorGridPanel({
		id : 'detailStore',
		store: orderDetailStore,
		title : '{s name="admin.action.order_details" namespace="sofort_multipay_backend"}{/s} '+orderNumber,
		plugins: checkColumn,
		cls: 'detailGrid',
		columns: [
			{ xtype: 'gridcolumn',id:'name',header: '{s name="admin.action.article" namespace="sofort_multipay_backend"}{/s}', width: 160, sortable: true, dataIndex: 'name'},
			{ xtype: 'gridcolumn',id:'articleordernumber',header: '{s name="admin.action.article_number" namespace="sofort_multipay_backend"}{/s}', width: 160, sortable: true, dataIndex: 'articleordernumber'},
			{ xtype: 'gridcolumn',id:'name',header: '{s name="admin.action.description" namespace="sofort_multipay_backend"}{/s}', width: 160, sortable: true, dataIndex: 'description'},
			editableAmountColumn(),
			editableQuantityColumn(),
			// { xtype: 'gridcolumn',id:'tax_total',header: '{s name="tax_value" namespace="sofort_multipay_backend"}{/s}', width: 160, sortable: true, renderer: Ext.util.Format.taxValue, dataIndex: 'tax'},
			// { xtype: 'gridcolumn',id:'articleordernumber',header: '{s name="net_value" namespace="sofort_multipay_backend"}{/s}', width: 160, sortable: true, renderer: Ext.util.Format.deMoney, dataIndex: 'netPrice'},
			{ xtype: 'gridcolumn',id: 'sum', header: '{s name="article_total" namespace="sofort_multipay_backend"}{/s}', width: 75, sortable: true, renderer: Ext.util.Format.deMoney, dataIndex: 'sum'},
			checkColumn
		],
		viewConfig: {
			forceFit: true
		},
		stripeRows: true,
		autoHeight: true,
		border: false,
		clicksToEdit: 1,
		initComponent: function() {
			sofortGrid.superclass.initComponent.call(this);
		},
		tbar: transactionFunctions(paymentStatus, customerProtection),
		bbar: cartFunctions(paymentStatus)
	});
	
	detailGrid.render(element);
};

function HistoryGridPanel(id, element) {
	this.transactionId = sofortStore.data.get(id).data.transactionId;
	
	historyStore = new Ext.data.Store({
		id: 'historyStore',
		url: "{url action=getTransactionHistory}",
		baseParams: {
			transactionId: this.transactionId
		},
		autoLoad: true,
		reader: new Ext.data.JsonReader({
			totalProperty: 'count',
			id: 'id',
			fields: [
				'date_modified', 'comment'
			]
		}),
		listeners: {
			'beforeload' : function(store, records, options) {
				loadingMask.show();
			},
			'load' : function(store, records, options) {
				loadingMask.hide();
			}
		}
	});
	
	
	var historyGrid = new Ext.grid.GridPanel({
		store: historyStore,
		title : '{s name="admin.action.invoice_history" namespace="sofort_multipay_backend"}{/s} '+this.transactionId,
		cls: 'historyGrid',
		columns: [
			{ xtype: 'gridcolumn',id:'time',header: 'Uhrzeit', width: 200, sortable: true, dataIndex: 'date_modified' },
			{ xtype: 'gridcolumn',id:'comment',header: 'Kommentar', dataIndex: 'comment', renderer: Ext.util.Format.commentRenderer},
		],
		viewConfig: {
			forceFit: true
		},
		stripeRows: true,
		autoExpandColumn: 'comment',
		autoHeight: true,
		border: true,
		initStore: function() {
			this.store.load();
		},
		initComponent: function() {
			var store = this.initStore();
			sofortGrid.superclass.initComponent.call(this);
		},
		init : function() {
			historyGrid.render(element);
			return historyGrid;
		},
		bbar : [{
			xtype: 'label', 
			html: '<div class="sofort-gradient" style="height: 20px; width: 100000000px;"></div>',
			width: '100%',
			labelWidth: 200
		}]
	});
	return historyGrid.init();
}
-->
</script>