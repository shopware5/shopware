<script type="text/javascript">
Shopware.Wizard.ProductsSelection = Ext.extend(Ext.grid.GridPanel,{
	title: 'Ausgewählte Artikel',
	//region: 'east',
	//margins: '5 0 5 0',
	//minSize: 100,
	flex:1,
	viewConfig: {
	    forceFit: true
	},
	ddGroup: 'firstGridDDGroup',
	enableDragDrop: true,
	stripeRows: true,
	columns: [
		{ header: '', width: 20, sortable: false, locked:true, renderer: function (v, p, r) {
    		return '<input type="checkbox" name="products_selection'+this.wizardID+'" value="'+r.data.id+'" style="float:left;margin-right:3px" />';
    	} },
		{ id:'ordernumber', dataIndex: 'ordernumber', header: "Artikelnummer", width: 120, sortable: true },
		{ id:'name', dataIndex: 'name', header: "Artikelname", width: 120, sortable: true },
		{ id:'supplier', dataIndex: 'supplier', header: "Hersteller", width: 120, sortable: true }
	],
	initComponent: function() {
		this.store = new Ext.data.Store({
			url: '{url action="getProducts"}',
			autoLoad: true,
			remoteSort: true,
			baseParams: { invert: 1, wizardID: this.wizardID },
			reader: new Ext.data.JsonReader({
				root: 'data',
				totalProperty: 'count',
				id: 'id',
				fields: [
					'id', 'ordernumber', 'name', 'supplier'
				]
			})
		});
		this.search = new Ext.form.TextField({
        	selectOnFocus: true,
        	width: 120,
        	listeners: {
            	'render': { fn:function(ob){
            		ob.el.on('keyup', function(){
					    this.store.baseParams['search'] = this.search.getValue();
					    this.store.load({ params:{ start:0, limit:25 } });
            		}, this, { buffer:500 });
            	}, scope:this }
        	}
        });
		this.bbar = new Ext.PagingToolbar({
	        pageSize: 25,
	        store: this.store,
	        displayInfo: true,
	        items:[
	            '-', 'Suche:&nbsp;', this.search
	        ]
	    });
	    this.tbar = [{
				text: 'Alle markieren',
				//iconCls:'add',
				handler: function (){
					$("input[@name=products_pool"+this.wizardID + "][type='checkbox']").attr('checked', true);
				},
				scope: this
			}, {
				text: 'Markierte Artikel entfernen',
				//iconCls:'add',
				handler: function (){
					var store = this.store;
					var targetStore = this.Parent.Pool.store;
					$("input[@name=products_selection"+this.wizardID+"][type='checkbox']:checked").each(function(index) {
						var recordId = $(this).val();
						var record = store.getById(recordId);
						store.remove(record);
						if(!targetStore.getById(recordId)) {
							$("input[@value="+recordId+"][type='checkbox']").attr('name', 'products_pool'+this.wizardID);
							targetStore.add(record);
						}
					});
				},
				scope: this
			}
        ];
	    Shopware.Wizard.ProductsSelection.superclass.initComponent.call(this);
	},
	afterRender: function(){
		var Pool = this;
		var secondGridDropTargetEl = this.getView().scroller.dom;
		var secondGridDropTarget = new Ext.dd.DropTarget(secondGridDropTargetEl, {
			ddGroup    : 'secondGridDDGroup',
			notifyDrop : function(ddSource, e, data){
				var records =  ddSource.dragData.selections;
				Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
				Pool.store.add(records);
				return true
			}
		});
		Shopware.Wizard.ProductsSelection.superclass.afterRender.call(this);
	}
});
</script>
