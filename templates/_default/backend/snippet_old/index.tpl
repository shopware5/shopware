{extends file="backend/index/parent.tpl"}

{block name="backend_index_javascript" append}
	<script type="text/javascript" src="{link file='backend/_resources/javascript/plugins/HtmlEntities.js'}"></script>
	<script type="text/javascript" src="{link file='backend/_resources/javascript/plugins/Ext.ux.FileUploadField.js'}"></script>
{/block}

{block name="backend_index_css" prepend}
	<link href="{link file='backend/_resources/styles/Ext.ux.FileUploadField.css'}" rel="stylesheet" type="text/css" />
{/block}

{block name="backend_index_javascript_inline"}
Ext.ns('Shopware.Snippet');

(function(){
	View = Ext.extend(Ext.Viewport, {
	    layout: 'border',
	    defaults: {
		    split: true,
		},
	    limitFilter: function() {
		    var snippetCount = Ext.getCmp("snippetCount");
		    this.store.baseParams["limit"] = snippetCount.getValue();
		    this.store.baseParams["start"] = 0;
		    Ext.getCmp("paging").pageSize = parseInt(snippetCount.getValue());
		    this.store.reload();
	    },
	    localeFilter: function(field, newValue,  oldValue ) {
		    this.store.baseParams["locale"] = newValue;
		    this.store.baseParams["start"] = 0;
		    this.store.reload();
	    },
	    searchFilter: function(textfield, e) {
	    	var search = Ext.getCmp("search");
	    	this.store.baseParams["search"] = search.getValue();
	    	this.store.baseParams["start"] = 0;
		    this.store.reload();
	    },
	    showEmptyFilter: function(button, e) {
	    	if(button.pressed) {
				this.store.baseParams["showEmpty"] = 1;
	    	}
	    	else {
	    		this.store.baseParams["showEmpty"] = 0;
	    	}
		    this.store.reload();
	    },
	   	getMarkedBoxes: function() {
	   		var ids = "";
			Ext.each(Ext.query('.markedSnippets'),function(checkbox,index) {
	    		if (checkbox.checked) {
	    			if(ids != "") {
	    				ids = ids +","+checkbox.value;
	    			}
	    			else {
	    				ids = +checkbox.value;
	    			}
	    		}
	    	});
	    	return ids;
	    },
	    deleteSnippets: function() {
	    	Ext.MessageBox.confirm('Bestätigung', 'Sollen die markierten Textbausteine wirklich gelöscht werden?', function deleteClientConfirmed(btn){
	    		if (btn=="yes") {
	    			var ids = Snippet.getMarkedBoxes();
	    			if(ids != "") {
	    				Ext.Ajax.request({
						   url: '{url module=backend controller=snippetOld action=deleteSnippets}',
						   success: function() {
						   		Ext.getCmp('snippet_grid').store.load();
						   },
						   params: { 'snippetIds': ids }
						});
	    			}
		    	}
    		});
	    },
	    addSnippet: function() {
		     snippetRecord = Ext.data.Record.create([
		        { name: 'id' },
	            { name: 'namespace' },
	            { name: 'name' },
	            { name: 'locale' },
	            { name: 'value' },
	            { name: 'shopID' },
	            { name: 'created' }
	        ]);

		    var defaultSnippet = new snippetRecord({
		        id: '',
				namespace: '',
				name: '',
		        locale: '',
		        value:'',
		        shopID: '1',
		        created: ''
	   	 	});

	        Ext.getCmp('snippet_grid').stopEditing();
	        Ext.getCmp('snippet_grid').store.insert(0, defaultSnippet);
			Ext.getCmp('snippet_grid').startEditing(0, 0);

	    },
	    changeSnippets: function() {
	    	Ext.MessageBox.prompt('Namespace', 'Bitte tragen Sie den neuen Namespace ein:',
          		function(btn,namespace) {
	          		var ids = Snippet.getMarkedBoxes();
	      			if(namespace != "" && ids != "") {
	          			Ext.Ajax.request({
						   url: '{url module=backend controller=snippetOld action=changeSnippets}',
						   success: function() {
						  		Ext.getCmp('snippet_grid').store.load();
						   },
						   params: { 'snippetIds': ids,'nameSpace':namespace}
					  	});
	      			}
          		});
	    },
	    dublicateSnippets: function() {
	    	Ext.MessageBox.prompt('Namespace', 'Bitte tragen Sie einen neuen Namespace ein:',
          		function(btn,namespace) {
          			var ids = Snippet.getMarkedBoxes();
          			if(namespace != "" && ids != "") {
	          			Ext.Ajax.request({
						   url: '{url module=backend controller=snippetOld action=dublicateSnippets}',
						   success: function() {
						  		Ext.getCmp('snippet_grid').store.load();
						   },
						   params: { 'snippetIds': ids,'nameSpace':namespace}
					  	});
          			}
          		});
	    },
	    updateDB: function(oGrid_Event,editData) {
			//submit to server
            Ext.Ajax.request({
                    waitMsg: 'Saving changes...',
                    url: '{url module=backend controller=snippetOld action=changeSnippet}',
                    params: {
                    	id: editData.id,
						namespace: editData.namespace,
						name: editData.name,
				        locale: editData.locale,
				        value: editData.value,
				        shopID: editData.shopID,
				        created: editData.created
                    },
                    failure:function(response,options){
                        Ext.MessageBox.alert('Warning','Oops...');
                    },
                    success:function(response,options){
						//if this is a new record need special handling
						if(oGrid_Event.record.data.id == 0){
							var options = Ext.decode(response.responseText);
							oGrid_Event.record.set('created',(options[1]));//created field
							oGrid_Event.record.set('id',(options[0])); // Row id
							Ext.getCmp('snippet_grid').store.commitChanges();
						} else {
							Ext.getCmp('snippet_grid').store.commitChanges();
						}
                    }
                 }
            );
        },
        showImExportWin: function() {
			this.imExportWin.show();
	    },
        renderOptions: function(value, p, r) {
			return '<a class="pencil" style="cursor:pointer;display:block; width:16px;height:16px;"></a>';
	    },
		renderHtmlEntities: function(val){
    		return htmlentities(val);
    	},
	    handleEdit: function(editEvent) {
			//determine what column is being edited
			var editData = editEvent.record.data;
			if(editData.namespace != "" && editData.locale != "" && editData.name != "" && editData.value != ""){
				var gridField = editEvent.field;
				this.updateDB(editEvent,editData);
			}

		},
		loadForm: function(grid,rowIndex,colIndex,e) {
			if(colIndex == 7) {
				var editData = grid.getSelectionModel().selection.record.data;
				this.editForm.getForm().load({
					url:'{url module=backend controller=snippetOld action=loadSnippetForm}',
					waitMsg:'Laden...',
				 	params: { 'nameSpace': editData.namespace,'name':editData.name}
				});
			}
		},
	    initComponent: function() {
	    	this.store = new Ext.data.Store({
    		   url: '{url module=backend controller=snippetOld action=getSnippet}',
		       // create reader that reads the Topic records
		       reader: new Ext.data.JsonReader({
		            root: 'data',
		            totalProperty: 'count',
		            id: 'readerID',
		            fields: [
		                'id','namespace','name','locale','value','shopID','created'
		           ]
		       }),
	       	   // turn on remote sorting
	           remoteSort: true
	    	});
	    	this.store.load();

	    	//////////////////////////////////////////
            //ImExportWindow
            //////////////////////////////////////////
            this.exportForm = new Ext.FormPanel({
		        title: 'Export',
		        bodyStyle:'padding:20px',
		        layout:'form',
		        id: 'exportForm',
		        defaults: { anchor: '80%'},
		        region:'north',
		        fileUpload: true,
		        height: 130,
		        items:[
	            {
		            	xtype: 'combo',
		            	id: 'formatExport',
		            	fieldLabel: 'Format',
		            	allowBlank: false,
		            	name:'formatExport',
		            	typeAhead: true,
		            	forceSelection: true,
		            	triggerAction: 'all',
		            	store:  new Ext.data.SimpleStore({
					        fields: ['formatArray'],
					        data : [['CSV'],['SQL(Backup)']]
					    }),
		            	displayField: 'formatArray',
		            	mode:'local',
		            	width: 120,
		            	selectOnFocus:true,
		            	listClass: 'x-combo-list-small'
	        		}
	        	],
		        buttonAlign:'right',
		        buttons: [{
		            text: 'Export',
		            handler: function(){
		            	var form = Ext.getCmp('exportForm').getForm();
		            	if(!form.isValid()) return;
			            form.submit( { url:' {url module=backend controller=snippetOld action=exportSnippet}', success: function (el, r){
			            }});
			        }
		        }]
		    });
		    this.importForm = new Ext.FormPanel({
		        region:'north',
        		fileUpload: true,
		        title: 'Import',
		        bodyStyle:'padding:20px',
		        layout:'form',
		        id: 'importForm',
		        defaults: { anchor: '80%'},
		        height: 130,
		        buttonAlign:'right',
		        items: [
		        	{
			            xtype: 'fileuploadfield',
			            emptyText: 'Bitte wählen...',
			            fieldLabel: 'Datei',
			            allowBlank: false,
			            id:'snippet_file',
			            name: 'snippet_file',
			            allowBlank: false,
			            buttonText: '',
			            buttonCfg: {
			                iconCls: 'upload-icon'
			            }
			        }
		        ],
		        buttons: [{
		            text: 'Import starten',
		            handler: function(){
		            var form = Ext.getCmp('importForm').getForm();
		            if(!form.isValid()) return;
		            	Ext.MessageBox.confirm('Bestätigung', 'Wollen Sie wirklich den Import durchführen?', function deleteClientConfirmed(btn){
		    				if (btn=="yes") {
					            form.submit({
				                    url:' {url module=backend controller=snippetOld action=importSnippet}',
				                    success: function(fp, o) {
				                    	Ext.Msg.alert('Result', o.result.msg);
				                        Ext.getCmp('impExportWin').hide();
				                    },
						    		failure: function (fp, o) {
			    						Ext.Msg.alert('Fehler', o.result.msg);
						    		}
				                });
		    				}
		            	})
		            }
		        }]
		    });


	    	this.imExportWin = new Ext.Window({
                layout:'fit',
                title:'Import / Export Textbausteine',
                width:500,
                height:300,
                id:'impExportWin',
                closeAction:'hide',
                autoScroll:true,
                plain: true,
                items: new Ext.Panel({
			    	autoScroll: true,
			    	border: false,
		    		items: [this.exportForm,this.importForm]
		    	})
            });



            //////////////////////////////////////////
            //ImExportWindow Ends
            //////////////////////////////////////////

	    	//init Treepanel
	    	this.tree = new Ext.tree.TreePanel({
                title: 'Filter',
                region: 'west',
                height: '100%',
                collapsible: true,
                width: 180,
                autoScroll:true,
                root: new Ext.tree.AsyncTreeNode( {
					 text: 'Namespace',
					 draggable:false,
					 id:'_'
				 } ),
                loader: new Ext.tree.TreeLoader( { dataUrl:'{url module=backend controller=snippetOld action=getNS}' } )
			});
			this.tree.on('click', function(e){
					this.store.baseParams = { nameSpace: e.attributes.id };
				 	this.store.load();
		    }, this);
	    	this.grid =  new Ext.grid.EditorGridPanel({
	    		id: 'snippet_grid',
                title: 'Textbausteine',
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
	            	'afteredit' : { fn: this.handleEdit, scope:this},
	            	'cellclick' : { fn: this.loadForm, scope:this}

            	},
                columns: [
		            {
			    		header: "",
			    		width: 25,
			    		sortable: false,
			    		locked:true,
			    		renderer: function (v,p,r,rowIndex,i,ds){
			    			return '<input type="checkbox" class="markedSnippets" name="markedSnippets" value="'+r.data.id+'" style="float:left;margin-right:3px" />';
			    		}
			    	},
	                {
                        xtype: 'gridcolumn',
                        dataIndex: 'namespace',
                        header: 'Namespace',
                        sortable: true,
                        width: 200,
                        renderer:  this.renderHtmlEntities,
                        editor: {
							allowBlank: false,
                            xtype: 'textfield'
                        }
                    },
                    {
                        xtype: 'gridcolumn',
                        dataIndex: 'locale',
                        header: 'Sprache',
                        sortable: true,
                        width: 60,
                        editor: {
                        	allowBlank: false,
			            	xtype: 'combo',
			            	id: 'language',
			            	typeAhead: false,
			            	forceSelection: false,
			            	triggerAction: 'all',
			            	 store:  new Ext.data.Store({
								url: '{url module=backend controller=snippetOld action=getLocales}',
								autoLoad: true,
							   	reader: new Ext.data.JsonReader({
							    	root: 'locales',
							        id: 'id',
							        fields: ['locale','locale']
							    })
				            }),
			            	valueField:'locale',
	                        displayField:'locale',
	                        mode: 'remote',
			            	width: 160,
			            	selectOnFocus:true,
			            	listClass: 'x-combo-list-small'
                        }
                    },
                    {
                        xtype: 'gridcolumn',
                        dataIndex: 'shopID',
                        header: 'Shop',
                        sortable: true,
                        width: 40,
                        editor: {
                        	allowBlank: false,
			            	xtype: 'combo',
			            	id: 'shopID',
			            	typeAhead: false,
			            	forceSelection: false,
			            	triggerAction: 'all',
			            	 store:  new Ext.data.Store({
								url: '{url module=backend controller=snippetOld action=getshopIDs}',
								autoLoad: true,
							   	reader: new Ext.data.JsonReader({
							    	root: 'shopIDs',
							        id: 'shopIDs',
							        fields: ['shopID','shopID']
							    })
				            }),
			            	valueField:'shopID',
	                        displayField:'shopID',
	                        mode: 'remote',
			            	width: 160,
			            	selectOnFocus:true,
			            	listClass: 'x-combo-list-small'
                        }
                    },
                    {
                        xtype: 'gridcolumn',
                        dataIndex: 'name',
                        header: 'Name',
                        sortable: true,
                        width: 200,
                        editor: {
                        	allowBlank: false,
                            xtype: 'textfield'
                        },
                        renderer:  this.renderHtmlEntities
                    },
                    {
                        xtype: 'gridcolumn',
                        dataIndex: 'value',
                        header: 'Inhalt',
                        sortable: true,
                        width: 280,
                        editor: {
                            allowBlank: false,
                            xtype: 'textfield'
                        },
                        renderer:  this.renderHtmlEntities
                    },
                    {
                        xtype: 'gridcolumn',
                        dataIndex: 'created',
                        header: 'Erzeugt',
                        sortable: true,
                        width: 120
                    },
                    {
                        xtype: 'gridcolumn',
                        dataIndex: 'option',
                        width: 30,
                        renderer: this.renderOptions
                    }
                ],
                bbar: {
                    xtype: 'paging',
                    store: this.store,
                    id:'paging',
                    pageSize: 25,
                    items: [
                    'Anzahl',
		            {
		            	xtype: 'combo',
		            	id: 'snippetCount',
		            	typeAhead: false,
		            	forceSelection: false,
		            	triggerAction: 'all',
		            	store:  new Ext.data.SimpleStore({
					        fields: ['limitArray'],
					        data : [['25'],['50'],['100'],['250'],['500'],['1000']]
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
	        	    'Sprache/Shop',
		            {
		            	xtype: 'combo',
		            	id: 'language',
		            	typeAhead: false,
		            	forceSelection: false,
		            	triggerAction: 'all',
		            	 store:  new Ext.data.Store({
							url: '{url module=backend controller=snippetOld action=getLanguageShop}',
							autoLoad: true,
						   	reader: new Ext.data.JsonReader({
						    	root: 'locales',
						        id: 'id',
						        fields: ['id','locale']
						    })
			            }),
		            	valueField:'id',
                        displayField:'locale',
                        mode: 'remote',
		            	width: 160,
		            	selectOnFocus:true,
		            	listClass: 'x-combo-list-small',
		            	listeners: {
			            	'change' : { fn: this.localeFilter, scope:this}
		            	}
		        	},
                    'Suche',
		            {
		            	xtype: 'textfield',
		            	id: 'search',
		            	selectOnFocus: true,
		            	width: 120,
		            	listeners: {
			            	'render': { fn:function(ob) {
			            		ob.el.on('keyup', this.searchFilter, this, { buffer:500});
			            	}, scope:this}
		            	}
		            },{
                        enableToggle: true,
                        text: 'Nur leere anzeigen',
                        listeners: {
			            	'click' : { fn: this.showEmptyFilter, scope:this}
		            	}
                    }
                    ]

                },
                tbar: {
                    xtype: 'toolbar',
                    items: [
	                    {
	                        xtype: 'button',
	                        text: 'Markierte Löschen',
	                        handler: this.deleteSnippets
	                    },
	                    '-'
	                    ,
	                    {
	                        xtype: 'button',
	                        text: 'NS für markierte ändern',
	                        handler: this.changeSnippets
	                    },
	                    '-'
	                    ,
	                    {
	                        xtype: 'button',
	                        text: 'Markierte duplizieren (Neuer NS)',
	                        handler: this.dublicateSnippets
	                    },
	                    '-'
	                    ,
	                    {
	                        xtype: 'button',
	                        text: 'Neuer Textbaustein',
	                        handler: this.addSnippet
	                    }
	                    ,'-',
	                    {
	                        xtype: 'button',
	                        text: 'Import/Export',
	                        listeners: {
			            		'click' : { fn: this.showImExportWin, scope:this}
		            		}
	                    }
                    ]
                }
            });

            this.editForm = new Ext.FormPanel({
                title: 'Details',
                id: 'editForm',
                border:false,
                split: true,
	            reader: new Ext.data.JsonReader({
					root: 'snippet',
					fields: [
						"namespace", "name","oldName","oldNamespace",{foreach item=translation from=$translations}'{$translation.name}',{/foreach}
					]
				}),
				height: 350,
	   			autoScroll:true,
   				layout:'form',
				region: 'south',
                items: [
                {
	            	layout:'column',
	            	height:35,
	            	bodyStyle:'padding:10px',
            		items:[
            		{
                		columnWidth:.5,
                		layout: 'form',
                		border:false,
                		items: [
                		{
		                    xtype:'textfield',
		                    fieldLabel: 'Namespace',
		                    width:300,
		                    name: 'namespace'
		                },
		                {
                	 		xtype:'hidden',
                 			name:'oldNamespace',
			         	}
		                ]
            		},
					{
                		columnWidth:.5,
                		layout: 'form',
                		border:false,
                		items: [
                		{
		                    xtype:'textfield',
		                    fieldLabel: 'Name',
		                    width:200,
		                    name: 'name'
		                },
		                {
                	 		xtype:'hidden',
                 			name:'oldName',
			         	}
		                ]
            		}]
                },
            	{
		            xtype:'fieldset',
		            title: 'Übersetzungen',
		            height:250,
		            autoScroll: true,
		            defaults: {
						anchor: '85%',
						xtype:'textarea'
		            },
		            items :[
		            {foreach item=translation from=$translations}
	                    {
	                        fieldLabel: '{$translation.label}',
	                        labelStyle: 'width:200px',
	                        name: '{$translation.name}'
	                    },
	                {/foreach}
                    ]
            	},
            	{
	                xtype: 'button',
	                width:80,
	                text: 'Speichern',
	                handler: function() {
						var form = Ext.getCmp('editForm').getForm();
						if(form.isValid()){
							form.submit({ url: '{url module=backend controller=snippetOld action=submitSnippet}', waitMsg:'Speichern...',
							 	success: function (e1,result) {
			            		}
							});
						}
					}
	            }
                ]
            });


	        this.items = [
            	this.tree,
            	{
					region: 'center',
					layout:'border',
	                items: [
		                this.grid,
		                this.editForm
	                ]
            	}
	        ];
	        View.superclass.initComponent.call(this);
	    }
	});
	Shopware.Snippet.View = View;
})();
Ext.onReady(function(){

	Snippet = new Shopware.Snippet.View;

});
{/block}
