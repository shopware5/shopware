<script type="text/javascript">
Shopware.Wizard.DetailProducts = Ext.extend(Ext.FormPanel,{
	title:'Artikel-Auswahl',
	layout:'hbox',
	layoutConfig: {
	    align : 'stretch',
	    pack  : 'start',
	},
	buttonAlign:'right',
	autoScroll: true,
	defaults: {
	    //collapsible: false,
	    //split: true,
	    //fitToFrame: true,
	    //autoHeight: true,
	},
	initComponent: function() {
		if(!this.wizardID) {
			this.disabled = true;
		}

		this.Categories = new Shopware.Wizard.ProductsCategories({ Parent: this, wizardID: this.wizardID });
		var Selection = this.Selection = new Shopware.Wizard.ProductsSelection({ Parent: this, wizardID: this.wizardID });
		var Pool = this.Pool = new Shopware.Wizard.ProductsPool({ Parent: this, wizardID: this.wizardID });
		this.items = [this.Categories, this.Selection, this.Pool];

		this.buttons = [{
            text: 'Alle verfügbaren Artikel hinzufügen',
            handler: function(){
            	var count = this.Pool.store.getTotalCount();
            	Ext.MessageBox.confirm('Bestätigung', 'Wollen Sie wirklich alle '+count+' Artikel hinzufügen?', function(r) {
					if(r!='yes') {
						return;
					}
					this.Pool.store.load({ params:{ start:0, limit:25, insert:1 }, callback: function() {
						this.Selection.store.load();
					}, scope: this });
				}, this);
	        },
       		scope:this
        },{
            text: 'Speichern',
            handler: function(){
            	var addProducts = [];
            	this.Selection.store.each(function(record, i){
					addProducts[i] = record.data.id;
				});
				var deleteProducts = [];
            	this.Pool.store.each(function(record, i){
					deleteProducts[i] = record.data.id;
				});
				$.ajax({
		    		url: '{url action="saveProducts"}',
		    		type: 'POST',
		    		context: this,
		    		data: { 'deleteProducts':deleteProducts, 'addProducts': addProducts, wizardID: this.wizardID },
		    		dataType: 'json',
		    		success: function(result) {
						this.Selection.store.load();
						this.Pool.store.load();
						Ext.MessageBox.show({
				           title: 'Hinweis',
				           msg: 'Artikel-Auswahl wurde erfolgreich gespeichert',
				           buttons: Ext.MessageBox.OK,
				           animEl: 'mb9',
				           icon: Ext.MessageBox.INFO
						});
		    		}
		    	});
	        },
       		scope:this
        }];

        this.fbar = {
	    	height: 45,
		    items: this.buttons
		};
		this.buttons = null;

		this.Categories.on('click', function(e){
 			this.Pool.store.baseParams = { wizardID: this.wizardID, categoryID: e.attributes.id };
		 	this.Pool.store.load();
		}, this);
		this.Selection.on('rowdblclick', function(grid, rowIndex, e){
			var record = this.Selection.store.getAt(rowIndex);
			this.Selection.store.remove(record);
			this.Pool.store.add(record);
		}, this);
		this.Pool.on('rowdblclick', function(grid, rowIndex, e){
			var record = this.Pool.store.getAt(rowIndex);
			this.Pool.store.remove(record);
			this.Selection.store.add(record);
		}, this);

		Shopware.Wizard.DetailProducts.superclass.initComponent.call(this);

		/*
		var firstGridDropTargetEl =  this.Selection.getView().scroller.dom;
		var firstGridDropTarget = new Ext.dd.DropTarget(firstGridDropTargetEl, {
			ddGroup    : 'firstGridDDGroup',
			notifyDrop : function(ddSource, e, data){
				var records =  ddSource.dragData.selections;
				Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
				Selection.store.add(records);
				return true
			}
		});

		var secondGridDropTargetEl = this.Pool.getView().scroller.dom;
		var secondGridDropTarget = new Ext.dd.DropTarget(secondGridDropTargetEl, {
			ddGroup    : 'secondGridDDGroup',
			notifyDrop : function(ddSource, e, data){
				var records =  ddSource.dragData.selections;
				Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
				Pool.store.add(records);
				return true
			}
		});
		*/
	}
});
</script>
