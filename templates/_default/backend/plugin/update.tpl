<script type="text/javascript">
Ext.ns('Shopware.Plugin');
(function(){
var UpdateWindow = Ext.extend(Ext.Window, {
    title: 'Nach Updates suchen',
	width: 900,
	height: 500,
	layout: 'border',
	closeAction: 'hide',
	plain: true,
	resizable:false,
	autoScroll:false,
	modal:true,
	createGridButton: function (value, id, record) {
			var cls = "ico package_go";
			new Ext.Button({
				text: value
				,iconCls: cls
				,handler : function(btn, e) {
					new Shopware.Plugin.DlUpdateWindow({ plugin: record.data.name}).show();
				}
			}).render(document.body, id);
	},
	error: function(message){
		Ext.Msg.show({
		   title:'Update-Fehler!',
		   msg: '<strong>Fehler-Protokoll: </strong><br />'+message,
		   buttons: Ext.Msg.OK,
		   animEl: 'elId',
		   icon: Ext.MessageBox.ERROR,
		   maxWidth: 700,
		   minWidth: 700
		});
	},
	searchUpdates: function(){
		Ext.Ajax.request({
		   url: '{url action=searchUpdates}',
		   scope: this,
		   success: function(response) {
			    if (response.responseText.match(/exception/)){
					this.error(response.responseText);
				}
			    Ext.getCmp('update_progress').updateProgress(1, "Suche abgeschlossen");
				Ext.TaskMgr.stop(this.task);
				this.grid.store.reload();
		   },
		   failure: function(response){
			    Ext.getCmp('update_progress').updateProgress(0, "Suche fehlgeschlagen");
				Ext.TaskMgr.stop(this.task);
		   }
		});
		var i = 0;
		this.task = {
			run: function(){
				i = i + 0.1;
				if (i > 0.9) i = 0;
				Ext.getCmp('update_progress').updateProgress(i, "Suche nach Updates");
			},
			interval: 250
		}
		Ext.TaskMgr.start(this.task);
	},
    initComponent: function() {
		this.progressbar = new Ext.ProgressBar({
		        text:'Suche nach Updates',
				region: 'north',
		        id:'update_progress'
		    });
	    var expander = new Ext.grid.RowExpander(
		{
        tpl : new Ext.Template(
            '<p><b>Changelog</b><br>',
            '<p>{literal}{changes}{/literal}</p>'
        )
    	}
		);
		this.renderOptions = function (value,id,r){

				  var id = Ext.id();
				  if(r.data.checkdate != '0000-00-00' && r.data.version != r.data.checkversion) {
						this.createGridButton.defer(1, this, ['Download Update', id, r]);
						return('<div id="' + id + '"></div>');
				  }else {
					  return "";
				  }
		};
		this.grid = new Ext.grid.GridPanel(
		{
			region: 'center',
			
			store: new Ext.data.Store({
	   			url: '{url action=getList path=Community sort=checkdate}',
	   			autoLoad: true,
	   			reader: new Ext.data.JsonReader({
	   				root: 'data',
	   				totalProperty: 'count',
	   				fields: [
	   					'id', 'path', 'namespace', 'name', 'autor','checkversion','checkdate','changes', 'version', 'active','added', 'copyright', 'license', 'label', 'source', 'support', 'link',
	   					{ name: 'update_date', type: 'date', dateFormat: 'timestamp' },
	        			{ name: 'installation_date', type: 'date', dateFormat: 'timestamp' }
	   				]
	   			})
	    	}),
			title: 'Community Plugins',
			plugins: expander,
			columns: [
				expander,
				{
	                xtype: 'gridcolumn',
	                header: 'Plugin-Name',
					dataIndex: 'name',
	                sortable: false,
	                width: 125,
					scope:this
	            },
	            {
	                xtype: 'gridcolumn',
	                header: 'Ihre Version',
					dataIndex: 'version',
	                sortable: false,
	                width: 85,
					scope:this,
	                renderer: function (v,p,r){
						if (v != r.data.checkversion)
						{
						return '<span style="color:#F00">'+v+'</span>';
						}
						return v;
					}
	            },
				{
	                xtype: 'gridcolumn',
	                header: 'Aktuelle Version',
					dataIndex: 'checkversion',
	                sortable: false,
	                width: 85,
					scope:this,
	                renderer: function (v,p,r){
						if (v != r.data.version)
						{
						return '<span style="color:#0F0">'+v+'</span>';
						}
						return v;
					}
	            },
				{
	                xtype: 'gridcolumn',
	                header: 'Datum Version',
					dataIndex: 'checkdate',
	                sortable: false,
	                width: 85,
					scope:this
	            },
				{
	                xtype: 'gridcolumn',
	                header: 'Optionen',
	                sortable: false,
	                width: 185,
					scope:this,
					renderer: this.renderOptions
				},{
	                xtype: 'gridcolumn',
	                header: 'Info',
	                sortable: false,
	                width: 150,
					scope:this,
	                renderer: function (v,p,r){
						if (r.data.checkdate == "0000-00-00"){
							return "Plugin nicht gefunden!";
						}
						else if (r.data.version == r.data.checkversion){
							return "Ihre Version ist aktuell";
						}
						else
						{
							return "Update verfügbar!";
						}
						return '';
					}
	            }
			]
		}
		);
		this.items = [this.progressbar, this.grid];
		this.on('afterrender', function(){
			Ext.getCmp('update_progress').reset();
			Ext.getCmp('update_progress').updateProgress(0.1, "Beleg-Erstellung läuft...");
			this.searchUpdates();
		});
	  UpdateWindow.superclass.initComponent.call(this);
	}
	});
	Shopware.Plugin.UpdateWindow = UpdateWindow;
})();
</script>