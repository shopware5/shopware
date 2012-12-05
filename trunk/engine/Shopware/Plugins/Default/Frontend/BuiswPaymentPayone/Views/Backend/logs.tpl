{extends file="backend/index/parent.tpl"}

{block name="backend_index_body_inline"}
	<script type="text/javascript">
		Ext.ns('BuiswPayOneLogs');

		(function(){
			View = Ext.extend(Ext.Viewport, {
				layout: 'border',
				defaults: {
					split: true
				},
				limitFilter: function() {
					var snippetCount = Ext.getCmp("BuiswPayOneLogsCount");

					this.store.baseParams["limit"] = snippetCount.getValue();
					this.store.baseParams["start"] = 0;
					Ext.getCmp("BuiswPayOneLogsPaging").pageSize = parseInt(snippetCount.getValue());

					this.store.reload();
				},
				searchFilter: function(textfield, e) {
					var search = Ext.getCmp("BuiswPayOneLogsSearch");

					this.store.baseParams["search"] = search.getValue();
					this.store.baseParams["start"] = 0;

					this.store.reload();
				}, 
				loadForm: function(grid,rowIndex,colIndex,e) {
					var editData = grid.getSelectionModel().selection.record.data;

					Ext.getCmp("BuiswPayOneLogsViewform").load({
						url:'{url action=loadFormData}',
						params: {
							'id':editData.id
						}
					});
				},
				initComponent: function() {
					this.store = new Ext.data.Store({
						url: '{url action=getApiLogs}',
						reader: new Ext.data.JsonReader({
							root: 'data',
							totalProperty: 'count',
							id: 'readerID',
							fields: [
								'id','occoured','api','request','response'
							]
						}),
					});
					this.store.load();

					this.grid =  new Ext.grid.EditorGridPanel({
						id: 'BuiswPayOneLogsGrid',
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
							header: 'Zeit',
							sortable: false,
							width: 250
						},{
							xtype: 'gridcolumn',
							dataIndex: 'api',
							header: 'Channel',
							sortable: false,
							width: 250
						},{
							xtype: 'gridcolumn',
							dataIndex: 'request',
							header: 'Request',
							sortable: false,
							width: 250
						},{
							xtype: 'gridcolumn',
							dataIndex: 'response',
							header: 'Response',
							sortable: false,
							width: 250
						}],
						bbar: {
							xtype: 'paging',
							store: this.store,
							id:'BuiswPayOneLogsPaging',
							pageSize: 25,
							items: [
							'Anzahl',{ 
								xtype: 'combo',
								id: 'BuiswPayOneLogsCount',
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
								id: 'BuiswPayOneLogsSearch',
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
						title: 'API Logs',
						id: 'BuiswPayOneLogsViewform',
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

			BuiswPayOneLogs.View = View;
		})();

		Ext.onReady(function(){
			Snippet = new BuiswPayOneLogs.View;
		});
	</script>
{/block}