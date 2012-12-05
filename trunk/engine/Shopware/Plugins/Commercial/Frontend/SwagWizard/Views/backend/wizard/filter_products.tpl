<script type="text/javascript">
Shopware.Wizard.FilterProducts = Ext.extend(Ext.grid.EditorGridPanel,{
	title:'Artikel-Werte',
	buttonAlign:'right',
	//autoScroll: true,
	stripeRows: true,
	clicksToEdit:1,
	initComponent: function() {

		if(!this.filterID) {
			this.disabled = true;
		}

        var cm = [
	        { id:'ordernumber', dataIndex: 'ordernumber', header: "Artikelnummer", width: 120, sortable: true },
	        { id:'name', dataIndex: 'name', header: "Artikelname", width: 200, sortable: true },
	        { id:'supplier', dataIndex: 'supplier', header: "Hersteller", width: 120, sortable: true }
        ];

        var values;
        $.ajax({
    		url: '{url action="getFilterValues"}',
    		type: 'POST',
    		context: this,
    		async: false,
    		data: { filterID: this.filterID },
    		dataType: 'json',
    		success: function(result) {
    			values = result.data;
    		}
    	});

        if(this.typeID==1 || this.typeID==3) {
	        $(values).each(function(){
        		cm[cm.length] = {
        			dataIndex: 'score_'+this.id,
        			header: this.value,
        			width: 120, sortable: true,
        			editor: new Ext.form.NumberField({ maxValue: 1, decimalPrecision: 0, allowNegative: false }),
        			renderer: function (value) { return value ? '1' : '0'; }
        		};
        	});
        } else {
        	$(values).each(function(){
        		cm[cm.length] = {
        			dataIndex: 'score_'+this.id,
        			header: this.value,
        			width: 120, sortable: true,
        			editor: new Ext.form.NumberField({ decimalPrecision: 0 })
        		};
        	});
        }

        this.columns = cm;

        var sf =  [
			'id', 'ordernumber', 'name', 'supplier', 'valueID'
		];

		$(values).each(function(){
    		sf[sf.length] = 'score_'+this.id;
    	});

		this.store = new Ext.data.Store({
			url: '{url action="getProducts"}',
			autoLoad: true,
			remoteSort: true,
			baseParams: { wizardID: this.wizardID, filterID: this.filterID },
			reader: new Ext.data.JsonReader({
				root: 'data',
				totalProperty: 'count',
				id: 'id',
				fields: sf
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
	            '-', 'Suche: ', this.search
	        ]
	    });
		this.buttons = [{
            text: 'Speichern',
            handler: function(){
            	var relations = [];
            	this.store.each(function(record, i){
					relations[i] = record.data;
				});
				$.ajax({
		    		url: '{url action="saveRelations"}',
		    		type: 'POST',
		    		context: this,
		    		data: { filterID: this.filterID, 'relations':relations },
		    		dataType: 'json',
		    		success: function(result) {
						this.store.load();
						Ext.MessageBox.show({
				           title: 'Hinweis',
				           msg: 'Artikel-Werte wurden erfolgreich gespeichert',
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

		Shopware.Wizard.FilterProducts.superclass.initComponent.call(this);
	}
});
</script>
