<script type="text/javascript">
Shopware.Wizard.FilterForm = Ext.extend(Ext.FormPanel,{
	labelWidth: 120,
    //frame:true,
    bodyStyle:'padding:5px 5px 0',
    title: 'Stammdaten',
    //autoHeight: true,
    //autoScroll: true,
    defaultType: 'textfield',
    layout:'anchor',
    defaults:{ anchor: '-20', defaults:{ anchor: '100%' } },
	initComponent: function() {

		if(this.typeID==3 || this.typeID==4) {
			var buttons;
			var listeners;
		} else {
			var buttons = [{
	   			text: 'Letzten Wert entfernen',
	   			iconCls:'delete',
	   			handler : function() {
	   				var store = this.Values.store;
	   				var count = store.getCount();
	   				if(count&&count>1) {
	   					store.remove(store.data.items[count-1]);
	   				}
	   			},
	        	scope:this
	   		},{
	   			text: 'Wert hinzufügen',
	   			iconCls:'add',
	   			handler : function() {
	   				var grid = this.Values;
	   				var store = this.Values.store;
	   				var count = store.getCount();

	   				var r = Ext.data.Record.create([
	   					{ name: 'key' },
	   					{ name: 'value' },
						]);
						var c = new r({
							key: count+1,
							value: "",
						});
						grid.stopEditing();
						grid.store.insert(count, c);
						grid.startEditing(count, 1);
	   			},
	        	scope:this
	   		}];
		};

		if(this.typeID<7) {
			this.Values = new Ext.grid.EditorGridPanel({
				clicksToEdit:1,
				height: 300,
				store: new Ext.data.Store({
					url: '{url action="getFilterValues"}',
					baseParams: { filterID: this.filterID, typeID: this.typeID },
					autoLoad: true,
					remoteSort: true,
					reader: new Ext.data.JsonReader({
						root: 'data',
						totalProperty: 'count',
						id: 'id',
						fields: [
							'id', 'key', 'value'
						]
					})
				}),
				stripeRows: true,
				columns: [
					{ id:'key', dataIndex: 'key', header: "&nbsp;", width: 120, sortable: true },
					{ id:'value', dataIndex: 'value', header: "Wert", width: 300, sortable: true, editor: new Ext.form.TextField({}) },
				],
		   		listeners : listeners,
		   		bbar: buttons
			});
		}

		this.filterTypeStore = new Ext.data.Store({
			url: '{url action="filterTypeList"}',
			//autoLoad: true,
		   	reader: new Ext.data.JsonReader({
		    	root: 'data',
		        totalProperty: 'count',
		        id: 'id',
		        fields: ['id', 'name']
		    })
        });

		this.fields = [{
			xtype:'textfield',
			fieldLabel: 'Name / Frage',
			name: 'name',
			allowBlank: false
		},{
			name: 'typeID',
			fieldLabel: 'Filtertyp',
            xtype: 'combo',
            hiddenName:'typeID',
            store:  this.filterTypeStore,
            emptyText:'Bitte wählen...',
            valueField: 'id',
            displayField: 'name',
            mode: 'remote',
            editable:false,
            selectOnFocus:true,
            triggerAction:'all',
            disabled: true,
            forceSelection : true
		},{
			fieldLabel: 'Beschreibung',
			xtype: "tinymce",
			height: 350,
			name:'description',
			value: ""
		},{
			xtype: 'combo',
			fieldLabel: 'Aktiv',
	        store: [
		        [0, 'Nein'],
		        [1, 'Ja']
	        ],
	        value: 1,
	        typeAhead: true,
	        forceSelection: true,
	        triggerAction: 'all',
	        selectOnFocus:true,
	        name:'active',
	        hiddenName:'active'
	    },{
			xtype:'numberfield',
			fieldLabel: 'Position',
			name: 'position'
		}];

		if(this.typeID==3 || this.typeID==4 || this.typeID==9) {
			this.fields[this.fields.length] = {
				xtype:'numberfield',
				fieldLabel: 'Bereich von',
				name: 'range_from',
				allowBlank: false,
				value: 1
			};
			this.fields[this.fields.length] = {
				xtype:'numberfield',
				fieldLabel: 'Bereich bis',
				name: 'range_to',
				allowBlank: false,
				value: this.typeID==9?100:5
			};
			this.fields[this.fields.length] = {
				xtype:'numberfield',
				fieldLabel: 'Schritte',
				name: 'steps',
				allowBlank: false,
				value: 1
			};
		}

		if(this.typeID==7 || this.typeID==10) {
			this.fields[this.fields.length] = {
				name: 'storeID',
				fieldLabel: 'Feld / Gruppe',
	            xtype: 'combo',
	            hiddenName:'storeID',
	            store:  new Ext.data.Store({
					url: '{url action="filterStoreList"}?typeID='+this.typeID,
					autoLoad: true,
				   	reader: new Ext.data.JsonReader({
				    	root: 'data',
				        totalProperty: 'count',
				        id: 'id',
				        fields: ['id', 'name']
				    })
	            }),
	            emptyText:'Bitte wählen...',
	            valueField: 'id',
	            displayField: 'name',
	            mode: 'remote',
	            selectOnFocus:true,
	            triggerAction:'all',
	            forceSelection : true
			};
		}

		this.items = [{
			xtype: 'fieldset',
			title: 'Einstellungen',
			items: this.fields
        }];
        if(this.Values) {
        	this.items[1] = {
				xtype: 'fieldset',
				title: 'Werte / Antworten',
				items: this.Values
	        };
        }

	    this.buttons = [{
	        text: 'Speichern',
	        handler: function(){
	        	this.getForm().findField('description').syncValue();

	        	var params = { id: this.filterID, wizardID: this.wizardID, typeID: this.typeID };
	        	if(this.Values) {
	        		this.Values.store.each(function(record){
	            		params['values['+record.data.key+']'] = record.data.value;
	            	});
	            	this.Values.store.commitChanges();
	        	}

	        	this.getForm().submit({ url: '{url action=saveFilter}', params: params, success: function (form, action){
		        		Ext.MessageBox.show({
				           title: 'Hinweis',
				           msg: 'Filter wurde erfolgreich gespeichert',
				           buttons: Ext.MessageBox.OK,
				           animEl: 'mb9',
				           icon: Ext.MessageBox.INFO
						});
						if (action.result.id != null){
				    		this.filterID = action.result.id;
						}

						if(this.Values) {
							this.Values.store.baseParams['filterID'] = this.filterID;
							this.Values.store.load();
						}

						Wizard.Tree.getNodeById('wizard_'+this.wizardID).reload();

						var text = form.findField('name').getValue();
						this.Parent.setTitle('Filter: '+text);

						if(this.Parent.Products) {
							this.Parent.remove(this.Parent.Products);
							this.Parent.Products.destroy();

							this.Parent.Products = new Shopware.Wizard.FilterProducts({
								Parent: this.Parent, wizardID: this.wizardID,
								filterID: this.filterID, typeID: this.typeID
							});
							this.Parent.add(this.Parent.Products);
						}
		        	},
				    failure: function(form, action) {
				        switch (action.failureType) {
				            case Ext.form.Action.CLIENT_INVALID:
				                //Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
				                break;
				            case Ext.form.Action.CONNECT_FAILURE:
				                Ext.Msg.alert('Failure', 'Ajax communication failed');
				                break;
				            case Ext.form.Action.SERVER_INVALID:
				            default:
				               Ext.Msg.alert('Failure', action.result.message);
				               break;
				       }
				    }, scope: this });
	        },
	        scope:this
	    }];
	    this.fbar = {
	    	height: 45,
		    items: this.buttons
		};
		this.buttons = null;

		Shopware.Wizard.FilterForm.superclass.initComponent.call(this);

		this.filterTypeStore.load({ callback: function() {
			this.load({ url: '{url action=getFilter}', params: { id: this.filterID, typeID: this.typeID } });
		}, scope: this });
	}
});
</script>
