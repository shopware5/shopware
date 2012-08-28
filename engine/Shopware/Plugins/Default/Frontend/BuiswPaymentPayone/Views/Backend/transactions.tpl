{extends file="backend/index/parent.tpl"}

{block name="backend_index_body_inline"}
	<script type="text/javascript">
		Ext.ns('BuiswPayOneTransactions');

		(function(){
			View = Ext.extend(Ext.Viewport, {
				layout: 'border',
				defaults: {
					split: true
				},
				limitFilter: function() {
					var snippetCount = Ext.getCmp("BuiswPayOneTransactionsCount");

					this.store.baseParams["limit"] = snippetCount.getValue();
					this.store.baseParams["start"] = 0;
					Ext.getCmp("BuiswPayOneTransactionsPaging").pageSize = parseInt(snippetCount.getValue());

					this.store.reload();
				},
				searchFilter: function(textfield, e) {
					var search = Ext.getCmp("BuiswPayOneTransactionsSearch");

					this.store.baseParams["search"] = search.getValue();
					this.store.baseParams["start"] = 0;

					this.store.reload();
				}, 
				loadForm: function(grid,rowIndex,colIndex,e) {
					var editData = grid.getSelectionModel().selection.record.data;

					Ext.getCmp("BuiswPayOneTransactionsViewform").load({
						url:'{url action=loadTransactionsFormData}',
						params: {
							'id':editData.id
						}
					});
				},
				initComponent: function() {
					this.store = new Ext.data.Store({
						url: '{url action=getTransactionsLogs}',
						reader: new Ext.data.JsonReader({
							root: 'data',
							totalProperty: 'count',
							id: 'readerID',
							fields: [
								'id','occoured','order_number','transaction_no','paymethod', 'customer_email', 'amount', 'status'
							]
						}),

						remoteSort: true
					});
					this.store.load();

					this.grid =  new Ext.grid.EditorGridPanel({
						id: 'BuiswPayOneTransactionsGrid',
						height: 400,
						autoScroll:true,
						region: 'center',
						split: true,
						store: this.store,
						monitorValid: true,
						errorSummary: true,
						forceValidation: true,
						clicksToEdit: 2,
						listeners: { 
							'cellclick' : { fn: this.loadForm, scope:this}
						},
						columns: [{
							type: 'gridcolumn',
							dataIndex: 'id',
							header: "ID",
							hidden:true,
							sortable: false
						},{
							xtype: 'gridcolumn',
							dataIndex: 'occoured',
							header: 'Zeitpunkt',
							sortable: false,
							width: 250
						},{
							xtype: 'gridcolumn',
							dataIndex: 'order_number',
							header: 'Bestellnummer',
							sortable: false,
							width: 250
						},{
							xtype: 'gridcolumn',
							dataIndex: 'transaction_no',
							header: 'Transaktionsnummer',
							sortable: false,
							width: 250
						},{
							xtype: 'gridcolumn',
							dataIndex: 'paymethod',
							header: 'Zahlmethode',
							sortable: false,
							width: 250
						},{
							xtype: 'gridcolumn',
							dataIndex: 'customer_email',
							header: 'Kunden-E-Mail',
							sortable: false,
							width: 250
						},{
							xtype: 'gridcolumn',
							dataIndex: 'amount',
							header: 'Betrag',
							sortable: false,
							width: 250
						},{
							xtype: 'gridcolumn',
							dataIndex: 'status',
							header: 'Status',
							sortable: false,
							width: 250
						}],
						bbar: {
							xtype: 'paging',
							store: this.store,
							id:'BuiswPayOneTransactionsPaging',
							pageSize: 25,
							items: [
							'Anzahl',{ 
								xtype: 'combo',
								id: 'BuiswPayOneTransactionsCount',
								typeAhead: false,
								forceSelection: false,
								triggerAction: 'all',
								store:  new Ext.data.SimpleStore({ 
									fields: ['limitArray'],
									data : [['5'],['10'],['25'],['50']]
								}),
								displayField: 'limitArray',
								lazyRender: false,
								lazyInit: false,
								mode:'local',
								width: 120,
								selectOnFocus:true,
								listClass: 'x-combo-list-small',
								listeners: { 
									'change' : { fn: this.limitFilter, scope:this}
								}
							},
							'Suche',{
								xtype: 'textfield',
								id: 'BuiswPayOneTransactionsSearch',
								selectOnFocus: true,
								width: 120,
								listeners: {
									'render': { fn:function(ob) {
										ob.el.on('keyup', this.searchFilter, this, { buffer:500});
									}, scope:this}
								}
							}]
						}
					});

					this.editForm = new Ext.Panel({
						title: 'Transaktionen',
						id: 'BuiswPayOneTransactionsViewform',
						border:false,
						split: true,
						html:'',
						height: 350,
						autoScroll:true,
						region: 'south'
					});

					this.items = [{
						region: 'center',
						layout:'border',
						items: [
							this.grid,
							this.editForm
						]
					}];

					View.superclass.initComponent.call(this);
				}
			});

			BuiswPayOneTransactions.View = View;
		})();

		Ext.onReady(function(){
			Snippet = new BuiswPayOneTransactions.View;
		});
	</script>
{/block}