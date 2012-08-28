<script type="text/javascript">
Ext.ns('Shopware.Plugin');
(function(){
	var List = Ext.extend(Ext.grid.GridPanel, {
	    title: 'Verfügbare Plugins',
	    stripeRows:true,
	    createGridButton: function (value, id, record) {
			if (value == "Install"){
				var cls = "ico package_add";
			}else  {
				var cls = "ico package_delete";
			}
			new Ext.Button({
				text: value
				,iconCls: cls
				,handler : function(btn, e) {
					if (!record.data.installation_date){
						Viewport.installPlugin(record.data.id, true);
					}else {
						Viewport.installPlugin(record.data.id, false);
					}
				}
			}).render(document.body, id);
		},
		createGridButtonEdit: function (value, id, record) {
			var cls = "ico package_go";
			new Ext.Button({
				text: value
				,iconCls: cls
				,handler : function(btn, e) {
					Viewport.showDetail(record.data.id);
				}
			}).render(document.body, id);
		},
		createGridButtonDelete: function (value, id, record) {
			var cls = "ico package_delete";
			new Ext.Button({
				text: value
				,iconCls: cls
				,handler : function(btn, e) {
					Viewport.removePlugin(record.data.id);
				}
			}).render(document.body, id);
		},
	    initComponent: function() {
	    	Ext.QuickTips.init();
	    	var selModel = this.selModel = new Ext.grid.RowSelectionModel({ singleSelect: true });
	    	
	    	this.store = new Ext.data.Store({
	   			url: '{url action=getList}',
	   			autoLoad: true,
	   			reader: new Ext.data.JsonReader({
	   				root: 'data',
	   				totalProperty: 'count',
	   				id: 'id',
	   				fields: [
	   					'id', 'path', 'namespace', 'name', 'autor','checkversion','version', 'active','added', 'copyright', 'license', 'label', 'source', 'support', 'link',
	   					{ name: 'update_date', type: 'date', dateFormat: 'timestamp' },
	        			{ name: 'installation_date', type: 'date', dateFormat: 'timestamp' }
	   				]
	   			})
	    	});

			function nl2br (str, is_xhtml) {
				 var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br />';
   				 return (str + '').replace(/(\n)/g, '$1' + breakTag)
			}
			
			this.store.on('exception', function (misc,type,action,options,response){
				var text = nl2br(response.responseText,true);
				var code = response.status;
				var info = "Bitte beheben Sie den Fehler oder löschen Sie das fehlerhafte Plugin!";
				Ext.Msg.show({
				   title:'Fehler! Plugin-Liste konnte wegen eines defekten Plugins nicht geladen werden',
				   msg: '<strong>Fehler-Protokoll: </strong><br />'+text+info,
				   buttons: Ext.Msg.OK,
				   animEl: 'elId',
				   icon: Ext.MessageBox.ERROR,
				   maxWidth: 700,
				   minWidth: 700
				});
			});
	    	
	    	this.getView().getRowClass = function(record, index) {
	    		if (!record.data.active) {
					if (!record.data.installation_date){
	    				return 'inactive';
					} else {
						return 'red';
					}
	    		}else {
					return 'green';
				}
	    	};

			this.on('rowdblclick', function(grid,rowIndex,e){
				if(!rowIndex) rowIndex = '0';
				var rec = grid.getStore().getAt(rowIndex);
				Viewport.showDetail(rec.get('id'));
			});
			this.updateWindow = new Shopware.Plugin.UpdateWindow();
			this.tbar = new Ext.Toolbar( {
				items: new Ext.Button({
					text: 'Nach Updates suchen',
					handler: function(){
						this.updateWindow.show();

					},
					scope: this
				})
			});
	    	this.bbar = new Ext.PagingToolbar({
	    		pageSize: 25,
	    		store: this.store,
	    		displayInfo: true,
	    		items:[
		    		'-', 'Suche: ',
		    		{
		    			xtype: 'textfield',
		    			id: 'usersearch',
		    			selectOnFocus: true,
		    			width: 120,
		    			listeners: {
		    			'render': { fn:function(ob){
		    				ob.el.on('keyup', function(){
		    					var search = Ext.getCmp("usersearch");
		    					this.store.baseParams["path"] = '';
		    					this.store.baseParams["search"] = search.getValue();
		    					this.store.load({ params:{ start:0, limit:25 } });
		    				}, this, { buffer:500 });
		    			}, scope:this }
		    			}
		    		}
	    		]
	    	});

   			this.renderInstall = function (value,id,r){
				  var id = Ext.id();

				  if(!r.data.installation_date) {

					  this.createGridButton.defer(1, this, ['Install', id, r]);
					  return('<div id="' + id + '"></div>');
				  }else {
						this.createGridButton.defer(1, this, ['Uninstall', id, r]);
                		return('<div id="' + id + '"></div>');
				  }
			};

			this.renderEdit = function (value,id,r){
				  var id = Ext.id();
				  if(!r.data.installation_date) {
					  return "";
				  }else {
						this.createGridButtonEdit.defer(1, this, ['Bearbeiten', id, r]);
                		return('<div id="' + id + '"></div>');
				  }
			};
			this.renderDelete = function (value,id,r){
				
				  var id = Ext.id();
				  if(!r.data.installation_date && r.data.source != "Default") {
						this.createGridButtonDelete.defer(1, this, ['Entfernen', id, r]);
						return('<div id="' + id + '"></div>');
				  }else {
					  return "";
				  }
			};

			
	        this.columns = [
	            {
	                xtype: 'gridcolumn',
	                header: 'Installation',
	                sortable: false,
	                width: 85,
					scope:this,
					renderer: this.renderInstall
	            },
				
	         	{
	                xtype: 'gridcolumn',
	                dataIndex: 'label',
	                header: 'Name',
	                sortable: false,
	                width: 150,
	                renderer: function (v,p,r){
						p.attr = 'ext:qtip="Installationsdatum:'
						 + Ext.util.Format.date(r.data.installation_date,'d.m.Y')
						 + '<br />Lizenz: '+r.data.license
						 + '" ext:qtitle="'+r.data.label+'"';
	                	return '<span style="font-weight:bold">' + v + "</span";
	                }
	            },
				{
	                xtype: 'gridcolumn',
	                dataIndex: 'added',
	                header: 'Hinzugefügt',
	                sortable: false,
	                width: 150,
	                renderer: function (v,p,r){
						if (v == "0000-00-00 00:00:00") return "";
	                	return "<span style=\"font-weight:bold\">"+v+"</span";
	                }
	            },
				{
	                xtype: 'booleancolumn',
	                dataIndex: 'active',
	                header: 'Aktiv',
	                sortable: false,
	                width: 75,
	                trueText: 'ja',
	                falseText: 'nein'
	            },
				{
	                xtype: 'gridcolumn',
	                header: 'Optionen',
	                sortable: false,
	                width: 95,
					scope:this,
					renderer: this.renderEdit
			    }, {
	                xtype: 'gridcolumn',
	                dataIndex: 'version',
	                header: 'Ihre Version',
	                sortable: false,
	                width: 95,
					editable: false,
					align: 'left',
	                renderer: function (v,p,r){
						if (r.data.source == "Default"){
							return "Shopware 3.5.4";
						}
						return v;
					}
	            }, {
	                xtype: 'gridcolumn',
	                dataIndex: 'checkversion',
	                header: 'Aktuelle Version',
	                sortable: false,
	                width: 95,
					editable: false,
					align: 'left',
	                renderer: function (v,p,r){
						if (r.data.source != "Community"){
							return "-";
						}
						if (v != '') return v;
						return '?';
					}
	            }, {
	                xtype: 'gridcolumn',
	                dataIndex: 'path',
	                header: 'Pfad',
	                sortable: false,
	                width: 200
	            }, {
	                xtype: 'gridcolumn',
	                dataIndex: 'autor',
	                header: 'Hersteller',
	                sortable: false,
	                width: 100,
	                renderer: function (value, p, record){
	                	if(!record.data.link) {
	                		return record.data.autor;
	                	}
	                	return '<a h'+'ref="'+record.data.link+'" target="_blank">'+record.data.autor+'</a>';
	                }
	            }, {
	                xtype: 'gridcolumn',
	                header: 'Löschen',
	                sortable: false,
	                width: 95,
					scope:this,
					renderer: this.renderDelete
			    }
	        ];
	        List.superclass.initComponent.call(this);
	    }
	});
	Shopware.Plugin.List = List;
})();
</script>