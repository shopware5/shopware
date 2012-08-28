<script type="text/javascript">
Ext.ns('Shopware.Plugin');
(function(){
var DlUpdateWindow = Ext.extend(Ext.Window, {
    title: 'Update downloaden',
	width: 900,
	height: 500,
	layout: 'border',
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
					var download = record.data.download;
					window.open(download);
				}
			}).render(document.body, id);
	},
	initComponent: function() {
		this.renderOptions = function (value,id,r){
			var id = Ext.id();
			this.createGridButton.defer(1, this, ['Download', id, r]);
			return('<div id="' + id + '"></div>');
		};
        this.Form = new Ext.form.FormPanel({
        	title: 'Ihre Shopware-ID Zugangsdaten',
        	autoScroll:true,
			height: 200,
			margin: '20 20 20 20',
			bodyStyle: "padding: 15px;",
    		region: 'north',
        	items: [
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Shopware-ID',
                        name: 'user',
                        width: 220,
						id: 'user',
						allowBlank:false
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Passwort',
                        name: 'password',
                        width: 220,
						id: 'password',
						allowBlank:false
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Domain',
						value: '{config name=host}',
                        name: 'domain',
						id: 'domain',
                        width: 220,
						allowBlank:false
                    }
			],
			buttons: [
				{
					text: 'Downloads suchen',
					handler: function(){
						
						if (this.Form.getForm().isValid()){
							var user = Ext.getCmp('user').getValue();
							var password = Ext.getCmp('password').getValue();
							var host = Ext.getCmp('domain').getValue();
							var plugin = this.plugin;

							this.grid.enable();

							this.grid.store.baseParams.user = user;
							this.grid.store.baseParams.password = password;
							this.grid.store.baseParams.host = host;
							this.grid.store.baseParams.plugin = plugin;
							this.grid.store.reload();
																												
						}

					},
					scope: this
				}
			]
		});

		this.grid = new Ext.grid.GridPanel(
		{
			region: 'center',
			disabled: true,
			store: new Ext.data.Store({
	   			url: '{url action=getUpdateDownloads}',
	   			autoLoad: false,
	   			reader: new Ext.data.JsonReader({
	   				root: 'downloads',
	   				totalProperty: 'count',
	   				fields: [
	   					'typ', 'download', 'filename'
	   				]
	   			}),
				listeners: {
					exception: function (misc,type,action,options,response){
							function nl2br (str, is_xhtml) {
								 var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br />';
								 return (str + '').replace(/(\n)/g, '$1' + breakTag)
							}
							var text = nl2br(response.responseText,true);
							var code = response.status;
							Ext.Msg.show({
							   title:'Fehler! Es konnten keine Downloads ermittelt werden!',
							   msg: '<strong>Fehler-Protokoll: </strong><br />'+text,
							   buttons: Ext.Msg.OK,
							   animEl: 'elId',
							   icon: Ext.MessageBox.ERROR,
							   maxWidth: 700,
							   minWidth: 700
							});
						}
					}
				}
	    	),
			title: 'Verfügbare Update-Packages (Download 30 Minuten gültig)',
			columns: [
				{
	                xtype: 'gridcolumn',
	                header: 'Art',
					dataIndex: 'typ',
	                sortable: false,
	                width: 125,
					scope:this
	            },
				{
	                xtype: 'gridcolumn',
	                header: 'Dateiname',
					dataIndex: 'filename',
	                sortable: false,
	                width: 125,
					scope:this
	            },
				{
	                xtype: 'gridcolumn',
	                header: 'Optionen',
					dataindex: 'download',
	                sortable: false,
	                width: 185,
					scope:this,
					renderer: this.renderOptions
				}
			]
		}
		);
		this.items = [this.Form, this.grid];
		DlUpdateWindow.superclass.initComponent.call(this);
		}
	});
	Shopware.Plugin.DlUpdateWindow = DlUpdateWindow;
})();
</script>