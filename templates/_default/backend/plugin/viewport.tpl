<script type="text/javascript">
Ext.ns('Shopware.Plugin');
(function(){
	var Viewport = Ext.extend(Ext.Viewport, {
	    layout: 'border',
	    initComponent: function() {
	    	this.list = new Shopware.Plugin.List;
	    	this.upload = new Shopware.Plugin.Upload;
			this.communityStore = new Ext.ux.IFrameComponent({ 
				title:'Shopware CommunityStore',
				autoScroll:true,
				height: 600,
				width: 1000,
				url: 'http://store.shopware.de',
			});
			
			this.store = new Ext.Panel({
				autoScroll:true,
				title: 'Shopware CommunityStore',
				items: [
					this.communityStore
				],
				tbar: [
					new Ext.Button  ({
		            	text: 'Store im neuen Fenster öffnen',
		            	handler: function(){
		            		window.open("http://store.shopware.de/");
		            	},
		            	scope:this
	             	})
				]
			});
	
	    	this.tree = new Ext.tree.TreePanel({
	    		title: 'Verzeichnisse',
	    		width: 248,
				border: true,
				animate: true,
				useArrows: true,
				split:true,
				collapsible: true,
				iconCls: 'ico package',
	    		region: 'west',
	    		rootVisible:false,
	    		root: {
	    			id: '0'
	    		},
	    		loader: {
	    			url: '{url action="getTree"}'
	    		},
                listeners: {
                    'click': { scope:this, fn:function(el) {
                    	this.list.store.baseParams["search"] = '';
                    	this.list.store.baseParams["path"] = el.id;
                    	this.list.store.load({ params:{ start:0, limit:20 } });
                    } }
                }
	    	});
	    	this.tabpanel = new Ext.TabPanel({
	    		activeTab: 0,
	    		region: 'center',
	    		enableTabScroll: true,
	    		items: [
		    		this.list, this.upload,this.store
	    		]
	    	});
	        this.items = [
	        	this.tree,
	        	this.tabpanel
	        ];
	        
		    this.showDetail = function(pluginId) {
		    	$.ajax({
		    		url: '{url action="detail"}',
		    		context: this,
		    		data: { id: pluginId },
		    		dataType: 'jsonp',
		    		success: function(tab) {
						Ext.QuickTips.init();
		    			this.tabpanel.remove(tab.id);
		    			this.tabpanel.add(tab);
		    			this.tabpanel.activate(tab.id);
		    		}
		    	});
		    };
		    
		    this.refreshList = function() {
		    	this.list.store.load();
		    };

			this.removePlugin = function(pluginId){
				Ext.MessageBox.confirm('', 'Soll dieses Plugin wirklich gelöscht werden?', function(r){
					if(r!='yes') {
						return;
					}
					$.ajax({
			    		url: '{url action=delete}',
			    		method: 'post',
			    		context: this,
			    		data: { id: pluginId },
			    		dataType: 'json',
			    		success: function(result) {
							Ext.Msg.show({
							   title:'Hinweis!',
							   msg: 'Das Plugin wurde aus dem Dateisystem entfernt!',
							   buttons: Ext.Msg.OK,
							   animEl: 'elId',
							   icon: Ext.MessageBox.ERROR,
							   maxWidth: 700,
							   minWidth: 700
							});
							Viewport.refreshList();
						},
						error: function(result){
							Ext.Msg.show({
							   title:'Es ist ein kritischer Fehler aufgetreten!',
							   msg: '<strong>Fehler-Protokoll: </strong><br />'+result.responseText,
							   buttons: Ext.Msg.OK,
							   animEl: 'elId',
							   icon: Ext.MessageBox.ERROR,
							   maxWidth: 700,
							   minWidth: 700
							});
						}
					});
				});
			};
			
			this.doUninstallPlugin = function(params) {
				Ext.Ajax.request({
					url: '{url action=uninstall}',
					method: 'post',
					params: params,
					scope: this,
					success: function(response, options) {
						var result = Ext.decode(response.responseText);
	   					if(result.success) {
	   						Ext.MessageBox.alert('Plugin deinstallieren', 'Das Plugin wurde erfolgreich deinstalliert.');
	   						this.refreshList();
	   					} else {
	   						Ext.Msg.show({
	   							title: 'Plugin konnte nicht deinstalliert werden!',
	   							msg: '<strong>Fehler: </strong><br /><br />' + result.message,
	   							buttons: Ext.Msg.OK,
	   							icon: Ext.MessageBox.ERROR,
	   							maxWidth: 700,
	   							minWidth: 400
	   						});
	   					}
					},
					failure: function(response, opts) {
						Ext.Msg.show({
						   title:'Es ist ein kritischer Fehler aufgetreten!',
						   msg: '<strong>Fehler: </strong><br /><br />'+response.responseText,
						   buttons: Ext.Msg.OK,
						   icon: Ext.MessageBox.ERROR,
						   maxWidth: 700,
						   minWidth: 400
						});
					}
				});
			}
			
			this.doInstallPlugin = function(params) {
				
				Ext.Ajax.request({
					url: '{url action=install}',
					method: 'post',
					params: params,
					scope: this,
					success: function(response, options) {
						var result = Ext.decode(response.responseText);
	   					if(result.success) {
	   						Ext.MessageBox.alert('Plugin installieren', 'Das Plugin wurde erfolgreich installiert.');
	   						this.refreshList();
		   					this.showDetail(params.id);
	   					} else if(result.license) {
	   						Ext.Msg.prompt(
	   							'Plugin installieren',
	   							'Das Plugin konnte nicht installiert werden.<br />'+
	   							result.message+
	   							'<br /><br />'+
	   							'Bitte geben Sie jetzt die Lizenz für das Modul "' + result.license_module +'" ein:',
	   							function(btn, value){
								    if (btn == 'ok'){
								    	params.license = value;
								    	params.license_module = result.license_module;
								    	this.doInstallPlugin(params);
								    }
	   							}, this
							);
	   					} else {
	   						if (!result.message){
								var message = 'Die Install-Methode hat "false" zurückgegeben. Bitte kontaktieren Sie den Plugin-Hersteller!';
							} else {
								var message = '<strong>Fehler: </strong><br /><br />' + result.message;
							}	
	   						Ext.Msg.show({
	   							title: 'Plugin konnte nicht installiert werden!',
	   							msg: message,
	   							buttons: Ext.Msg.OK,
	   							icon: Ext.MessageBox.ERROR,
	   							maxWidth: 700,
	   							minWidth: 400
	   						});
	   					}
					},
					failure: function(response, opts) {
						Ext.Msg.show({
						   title:'Es ist ein kritischer Fehler aufgetreten!',
						   msg: '<strong>Fehler: </strong><br /><br />' + response.responseText,
						   buttons: Ext.Msg.OK,
						   icon: Ext.MessageBox.ERROR,
						   maxWidth: 700,
						   minWidth: 700
						});
					}
				});
			}

		    this.installPlugin = function(pluginId, install) {
		    	if(install) {
					var message = 'Soll dieses Plugin installiert werden?';
				} else {
					var message = 'Soll dieses Plugin deinstalliert werden?';
				}
				Ext.MessageBox.confirm('', message, function(r){
					if(r!='yes') {
						return;
					}
					if(install) {
						this.doInstallPlugin({ id: pluginId });
					} else {
						this.doUninstallPlugin({ id: pluginId });
					}
				}, this);
		    };
	        Viewport.superclass.initComponent.call(this);
	    }
	});
	Shopware.Plugin.Viewport = Viewport;
})();
</script>