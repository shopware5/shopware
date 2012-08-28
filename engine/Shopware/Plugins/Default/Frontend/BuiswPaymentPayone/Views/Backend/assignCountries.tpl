{extends file="backend/index/parent.tpl"}

{block name="backend_index_body_inline"}
	{literal}
	<script type="text/javascript">
	//<![CDATA[
		//Ext.ns('Shopware.BuiswPayOne');
		{/literal}

		var title = '{$text}';
		var key = '{$key}';

		{literal}
		MyJsonStore = Ext.extend(Ext.data.JsonStore, {
			constructor: function(cfg) {
				cfg = cfg || {};
				MyJsonStore.superclass.constructor.call(this, Ext.apply({
					autoLoad: true,
					storeId: 'MyJsonStore',
					{/literal}
					url: '{url action=assign}' + cfg.target + '?key=' + key + '&assigned=' + cfg.assigned,
					{literal}
					root: 'data',
					totalProperty: 'count',
					fields: [{
						name: 'country'
					}]
				}, cfg));
			}
		}); // MyJsonStore()

		MyJsonStore2 = Ext.extend(Ext.data.JsonStore, {
			constructor: function(cfg) {
				cfg = cfg || {};
				MyJsonStore.superclass.constructor.call(this, Ext.apply({
					autoLoad: true,
					storeId: 'MyJsonStore2',
					{/literal}
					url: '{url action=assign}' + cfg.target + '?key=' + key + '&assigned=' + cfg.assigned,
					{literal}
					root: 'data',
					totalProperty: 'count',
					fields: [{
						name: 'shop'
					}]
				}, cfg));
			}
		}); // MyJsonStore2()

		MyJsonStore3 = Ext.extend(Ext.data.JsonStore, {
			constructor: function(cfg) {
				cfg = cfg || {};
				MyJsonStore.superclass.constructor.call(this, Ext.apply({
					autoLoad: true,
					storeId: 'MyJsonStore3',
					{/literal}
					url: '{url action=assign}' + cfg.target + '?key=' + key + '&assigned=' + cfg.assigned,
					{literal}
					root: 'data',
					totalProperty: 'count',
					fields: [{
						name: 'group'
					}]
				}, cfg));
			}
		}); // MyJsonStore3()

		/*
		MyJsonStore1 = Ext.extend(Ext.data.JsonStore, {
			constructor: function(cfg) {
				cfg = cfg || {};
				MyJsonStore.superclass.constructor.call(this, Ext.apply({
					autoLoad: true,
					storeId: 'MyJsonStore',
					url: 'http://bui-shopware-dev.de/WEB/store.php?' + cfg,
					root: 'data',
					totalProperty: 'count',
					fields: [{
						name: 'country'
					}]
				}, cfg));
			}
		}); // MyJsonStore()
		*/

		function buttonMoveHandler (b, target) {
			var panelLeft = Ext.getCmp (target + 'Z')
			var panelRight = Ext.getCmp (target + 'NZ')
			var from, to, fromStore, toStore;

			if (b.text == '<' || b.text == '<<') {
				from = panelRight.getSelectionModel();
				fromStore = panelRight.getStore();
				to = panelLeft.getStore();
			} else {
				from = panelLeft.getSelectionModel();
				fromStore = panelLeft.getStore();
				to = panelRight.getStore();
			}

			if (b.text == '<' || b.text == '>') {
				from.each (
					function (o) {
						to.add (o);
						fromStore.remove (o);
					}
				);
			} else {
				fromStore.each (
					function (o) {
						to.add (o) 
						fromStore.remove (o);
					}
				);
			}
		}


		MyViewportUi = Ext.extend(Ext.Viewport, {
			initComponent: function() {
				Ext.applyIf(this, {
					items: [{
						xtype: 'panel',
						height: 500,
						width: 474,
						autoScroll:true,
						title: title,
						//layout:'anchor',
						id: 'mainForm',
						items: [{
							xtype: 'panel',
							tpl: new Ext.XTemplate(''),
							height: 228,
							width: 381,
							layout: 'hbox',
							collapsible: true,
							title: 'L&auml;nderpanel',
							items: [{
								xtype: 'grid',
								id: 'laenderZ',
								height: 187,
								width: 142,
								title: 'Zugewiesen',
								ddGroup: 'dd_laenderZ',
								enableDragDrop: true,
								store: new MyJsonStore({target: 'Countries', assigned: 1}), // 'MyJsonStore',
								columns: [{
									xtype: 'gridcolumn',
									dataIndex: 'country',
									editable: false,
									header: 'Land',
									id: 'id',
									sortable: true,
									width: 100
								}]
							},{
								xtype: 'panel',
								height: 187,
								width: 83,
								layout: 'vbox',
								title: '< -- >',
								align: 'center',
								pack: 'center',
								items: [{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'laender');},
									text: '<'
								},{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'laender');},
									text: '>'
								},{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'laender');},
									text: '<<'
								},{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'laender');},
									text: '>>'
								}]
							},{
								xtype: 'grid',
								id: 'laenderNZ',
								height: 187,
								width: 142,
								title: 'nicht Zugewiesen',
								ddGroup: 'dd_laenderZ',
								enableDragDrop: true,
								store: new MyJsonStore({target: 'Countries', assigned: 0}),
								columns: [{
									xtype: 'gridcolumn',
									dataIndex: 'country',
									header: 'Land',
									id: 'id2',
									sortable: true,
									width: 100
								}]
							}]
						},{
							xtype: 'panel',
							tpl: new Ext.XTemplate(''),
							height: 228,
							width: 381,
							layout: 'hbox',
							collapsible: true,
							title: 'Mandantenpanel',
							items: [{
								xtype: 'grid',
								id: 'mandantZ',
								height: 187,
								width: 142,
								title: 'Zugewiesen',
								ddGroup: 'dd_mandantenZ',
								enableDragDrop: true,
								store: new MyJsonStore2({target: 'Shops', assigned: 1}),
								columns: [{
									xtype: 'gridcolumn',
									dataIndex: 'shop',
									editable: false,
									header: 'Shop',
									id: 'mid',
									sortable: true,
									width: 100
								}]
							},{
								xtype: 'panel',
								height: 187,
								width: 83,
								layout: 'vbox',
								title: '< -- >',
								align: 'center',
								pack: 'center',
								items: [{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'mandant');},
									text: '<'
								},{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'mandant');},
									text: '>'
								},{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'mandant');},
									text: '<<'
								},{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'mandant');},
									text: '>>'
								}]
							},{
								xtype: 'grid',
								id: 'mandantNZ',
								height: 187,
								width: 142,
								title: 'nicht Zugewiesen',
								ddGroup: 'dd_mandantenZ',
								enableDragDrop: true,
								store: new MyJsonStore2({target: 'Shops', assigned: 0}),
								columns: [{
									xtype: 'gridcolumn',
									dataIndex: 'shop',
									header: 'Shop',
									id: 'mid2',
									sortable: true,
									width: 100
								}]
							}]
						},{
							xtype: 'panel',
							tpl: new Ext.XTemplate(''),
							height: 228,
							width: 381,
							layout: 'hbox',
							collapsible: true,
							title: 'Kundengruppenpanel',
							items: [{
								xtype: 'grid',
								id: 'gruppeZ',
								height: 187,
								width: 142,
								title: 'Zugewiesen',
								ddGroup: 'dd_gruppenZ',
								enableDragDrop: true,
								store: new MyJsonStore3({target: 'Groups', assigned: 1}),
								columns: [{
									xtype: 'gridcolumn',
									dataIndex: 'group',
									editable: false,
									header: 'Kundengruppe',
									id: 'gid',
									sortable: true,
									width: 100
								}]
							},{
								xtype: 'panel',
								height: 187,
								width: 83,
								layout: 'vbox',
								title: '< -- >',
								align: 'center',
								pack: 'center',
								items: [{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'gruppe');},
									text: '<'
								},{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'gruppe');},
									text: '>'
								},{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'gruppe');},
									text: '<<'
								},{
									xtype: 'button', handler: function() {buttonMoveHandler (this, 'gruppe');},
									text: '>>'
								}]
							},{
								xtype: 'grid',
								id: 'gruppeNZ',
								height: 187,
								width: 142,
								title: 'nicht Zugewiesen',
								ddGroup: 'dd_gruppenZ',
								enableDragDrop: true,
								store: new MyJsonStore3({target: 'Groups', assigned: 0}),
								columns: [{
									xtype: 'gridcolumn',
									dataIndex: 'group',
									header: 'Kundengruppe',
									id: 'gid2',
									sortable: true,
									width: 100
								}]
							}]
						},{
							xtype: 'button',
							height: 63,
							width: 382,
							
							text: 'Speichern',
							listeners:{
								'click': function(){
									var s = Ext.getCmp ('laenderZ').getStore();
									var countries = [];
									s.each (function(o) {countries.push (o.id);});
									//console.log ('Laender', countries);
									Ext.Ajax.request({
										params: {key: key, 'countries[]': countries},
										url: 'saveAssignments', // {url action=saveConfig}',
										success: function(f,a){
											parent.parent.Growl ("Einstellungen wurden &uuml;bernommen!");
										},
										failure: function(f,a){
											if (a.failureType === Ext.form.Action.CONNECT_FAILURE)
												Ext.Msg.alert('Fehler', 'Server meldet:'+a.response.status+' '+a.response.statusText);

											if (a.failureType === Ext.form.Action.SERVER_INVALID)
												Ext.Msg.alert('Fehler', a.result.errormsg);
										}
									});



									// shops
									var s = Ext.getCmp ('mandantZ').getStore();
									var shops = [];
									s.each (function(o) {shops.push (o.id);});
									//console.log ('Shops', shops);
									Ext.Ajax.request({
										params: {key: key, 'shops[]': shops},
										url: 'saveAssignments', // {url action=saveConfig}',
										success: function(f,a){
											parent.parent.Growl ("Einstellungen wurden &uuml;bernommen!");
										},
										failure: function(f,a){
											if (a.failureType === Ext.form.Action.CONNECT_FAILURE)
												Ext.Msg.alert('Fehler', 'Server meldet:'+a.response.status+' '+a.response.statusText);

											if (a.failureType === Ext.form.Action.SERVER_INVALID)
												Ext.Msg.alert('Fehler', a.result.errormsg);
										}
									});

									// shops
									var s = Ext.getCmp ('gruppeZ').getStore();
									var groups = [];
									s.each (function(o) {groups.push (o.id);});
									//console.log ('Kundengruppen', groups);
									Ext.Ajax.request({
										params: {key: key, 'groups[]': groups},
										url: 'saveAssignments', // {url action=saveConfig}',
										success: function(f,a){
											parent.parent.Growl ("Einstellungen wurden &uuml;bernommen!");
										},
										failure: function(f,a){
											if (a.failureType === Ext.form.Action.CONNECT_FAILURE)
												Ext.Msg.alert('Fehler', 'Server meldet:'+a.response.status+' '+a.response.statusText);

											if (a.failureType === Ext.form.Action.SERVER_INVALID)
												Ext.Msg.alert('Fehler', a.result.errormsg);
										}
									});
								}
							}
						}]
					}]
				});

				MyViewportUi.superclass.initComponent.call(this);
			}
		});

		MyViewport = Ext.extend(MyViewportUi, {
			initComponent: function() {
				MyViewport.superclass.initComponent.call(this);
			}
		});

		Ext.onReady(function(){
			Ext.QuickTips.init();

			//var store = new MyJsonStore();
			//var store1 = new MyJsonStore1();
			var cmp1 = new MyViewport({
				renderTo: Ext.getBody()
			});

			cmp1.show();
		}); // ext.onReady()
	//]]>
	</script>
	{/literal}
{/block}
