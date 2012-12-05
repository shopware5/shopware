{if 1 != 1}<script>{/if}
Ext.tip.QuickTipManager.init();
 Ext.override(Ext.form.Field, {
  afterRender : function() {
	    
        if(this.helpText){
			if (this.hasRendered){
				this.callOverridden();
				return;
			}
			this.hasRendered = true;
            var myEl = new Ext.Element(document.createElement('img'));
            myEl.set({
                tag: 'img',
                src: '{link file='backend/b2bessentials/_resources/images/help.png'}',
                style: 'margin-left:'+this.helpMargin+';margin-top:'+this.helpMarginTop,
                width: 16,
                height: 16
            });

			myEl.appendTo(this.getEl().select('.x-form-item-body'));
			Ext.tip.QuickTipManager.register({
				target: myEl,
				title: 'Hilfe',
				text: this.helpText,
				width: 300,
				dismissDelay: 5000 // Hide after 10 seconds hover
			});
        }
        this.callOverridden();
  }
});
Ext.define('Shopware.B2B.PrivateShopping',
{
	extend: 'Ext.panel.Panel',
    initComponent: function(){
		Ext.define('Customergroups', {
				extend: 'Ext.data.Model',
				fields: [
					{ type: 'string', name: 'description'},
					{ type: 'string', name: 'groupkey'}
				]
		});

		 this.customerGroups = [
		 {foreach from=$customerGroupsWithRegistrationConfiguration item=customerGroup}
				{ "description":"{$customerGroup.description}","groupkey":"{$customerGroup.groupkey}"}{if !$customerGroup@last},{/if}
		 {/foreach}
		 ];
		 this.groupStore = Ext.create('Ext.data.Store', {
				model: 'Customergroups',
				data: this.customerGroups
		 });

		Ext.define('Templates', {
				extend: 'Ext.data.Model',
				fields: [
					{ type: 'string', name: 'name'},
					{ type: 'string', name: 'value'}
				]
		});

		this.TemplateStore = Ext.create('Ext.data.Store',
		{
			model: 'Templates',
			autoLoad: false,
			proxy: {
				// load using HTTP
				type: 'ajax',
				url: '{url controller=BusinessEssentials action=getTemplatesAvailable}',
				// the return will be XML, so lets set up a reader
				reader: {
					type: 'json',
					// records will have an "Item" tag
					root: 'data',
					totalRecords: 'count'
				}
			}
		}
		).load();

		Ext.apply(this, {
			title: 'Private Shopping Konfiguration',
			id: 'PrivateShopping',
			bodyStyle: 'padding-left: 10px;padding-top: 5px;padding-right:10px',
			listeners: {
				scope: this,

				/** Workaround to fix the annoying resize bug in extjs 4 */
				'resize': function(pnl, width, height) {
					var fieldsets = Ext.ComponentQuery.query('fieldset'),
						offset = 25;	// Padding, margin, scrollbar width etc.
					Ext.each(fieldsets, function(el, index) {
						el.setWidth(width - offset);
					});
				}
			},

			items: [
				{
					xtype: 'panel',
					html: '<strong style="font-weight:bold">Private Shopping</strong><br />Mit diesem Modul können Sie Ihren Shop, je nach Kundengruppe, mit einem zentralen Login schützen. Außerdem haben Sie die Möglichkeit für jede Kundengruppe ein eigenes Haupt-Template zu definieren, welches nach Login aktiv wird.',
					bodyStyle: 'padding-left: 10px;padding-top: 5px;padding-right:10px;border: none; padding-bottom:15px'
				},
				{foreach from=$customerGroups item=customerGroup}
				{

					xtype: 'fieldset',
					title: 'Kundengruppe "{$customerGroup.description}"',
					collapsible: true,
					defaults: {
						labelWidth: 89,
						anchor: '100%',
						layout: {
							type: 'hbox',
							defaultMargins: { top: 0, right: 5, bottom: 0, left: 0}
						}
					},
					items: [
						Ext.create('Ext.form.Panel', {
							url:'save-form.php',
							listeners: {
								'afterrender': function(form){
										this.loadForm(form);
								}
							},
							frame:true,
							title: 'Konfiguration',
							loadForm: function(form){
								form.load({ url: '{url action=loadPrivateShoppingConfig}/customergroup/{$customerGroup.groupkey}', success: function(form,action){
										if (!action.result.data.registerlink || action.result.data.registerlink == "0"){
											// Hiding underlying fields
											Ext.Array.each(form.owner.query('[hideIfRegisterNotAllowed="yes"]'),function(name,index,object){
												name.allowBlankBackup = name.allowBlank;
												name.allowBlank = true;
												name.hide();

											});
										}
										if (!action.result.data.unlockafterregister || action.result.data.unlockafterregister == "0"){

											Ext.Array.each(form.owner.query('[hideIfShopAccessIsAllowedImmediately="yes"]'),function(name,index,object){
												name.allowBlankBackup = name.allowBlank;
												name.allowBlank = true;
												name.hide();
											});
										}
								}
								});
							},
							bodyStyle:'padding:5px 5px',
							groupStore: this.groupStore,
							fieldDefaults: {
								msgTarget: 'side',
								labelWidth: 250
							},
							defaultType: 'textfield',

							/** Add save button to the form pnl */
							dockedItems: {
								dock: 'bottom',
								xtype: 'container',
								items: [{
									xtype: 'button',
									text: 'Speichern',
									padding: '0 25',
									scale: 'large',
									handler: function(e){
										this.up('form').getForm().submit(
										{
											url: '{url action=savePrivateShoppingConfig}/customergroup/{$customerGroup.groupkey}',
											success: function(form, action){
												Ext.Msg.alert('Info','Einstellungen wurden erfolgreich gespeichert');
											},
											failure: function(form,action){
												Ext.Msg.alert('Failed', action.result.msg);

											},
											scope: this
										});
									}
								},
								{
									xtype: 'button',
									text: 'Neu laden',
									padding: '0 25',
									margin: '20 20 20 20',
									scale: 'large',
									handler: function(e){
										this.up().up().loadForm(this.up().up());
									}
								}
								]
							},
							items: [
							{
								xtype: 'checkbox',
								fieldLabel: 'Zentrale Login-Seite vorschalten',
								name: 'activatelogin',
								width: '100',
								allowBlank:false,
								helpText: 'Aktivieren Sie diese Checkbox, um eine zentrale Login-Seite für diese Kundengruppe vorzuschalten.!',
								helpMargin: '5px',
								helpMarginTop: '-15px'
							},
							{
								xtype: 'textfield',
								fieldLabel: 'Controller/Action nach Login',
								name: 'redirectlogin',
								value: 'index/index',
								allowBlank:false,
								helpText: 'Hier definieren Sie, auf welchen Shop-View der Kunde nach Login umgeleitet werden soll. Default Startseite. Format Controller/Action!',
								helpMarginTop: '-15px',
								helpMargin: '200px',
								width: 450
							},
							{
								xtype: 'checkbox',
								fieldLabel: 'Registrierungsmöglichkeit auf der Login-Seite bereitstellen',
								name: 'registerlink',
								allowBlank:false,
								helpText: 'Zeigt den Registrierungslink für diese Kundengruppe auf der Login-Seite an.',
								helpMargin: '5px',
								helpMarginTop: '-15px',
								listeners: {
									change: function (a,checked,c){
										if (checked){
											Ext.Array.each(this.up().query('[hideIfRegisterNotAllowed="yes"]'),function(name,index,object){
												name.show();
												name.allowBlank = name.allowBlankBackup;
											});
										}else {
											Ext.Array.each(this.up().query('[hideIfRegisterNotAllowed="yes"]'),function(name,index,object){
												name.hide();
												name.allowBlankBackup = name.allowBlank;
												name.allowBlank = true;
											});
										}
									}
								}
							},
							{
								xtype: 'textfield',
								hideIfRegisterNotAllowed: 'yes',
								fieldLabel: 'Controller/Action nach Registrierung',
								name: 'redirectregistration',
								value: 'PrivateRegister/registerConfirmed',
								allowBlank:false,
								helpText: 'Hier definieren Sie, auf welche Shopseite der Kunde nach erfolgreicher Registrierung umgeleitet werden soll. Default: PrivateRegister/registerConfirmed. Format Controller/Action',
								helpMarginTop: '-15px',
								helpMargin: '200px',
								width: 450
							},
							{
								xtype: 'checkbox',
								fieldLabel: 'Shop-Zugriff nach Login erlauben',
								name: 'unlockafterregister',
								allowBlank:true,
								helpText: 'Wenn Sie diese Option aktivieren, ist der Zugriff auf den Shop nach erfolgreicher Registrierung sofort freischaltet!',
								helpMargin: '5px',
								helpMarginTop: '-15px',
								listeners: {
									change: function (a,checked,c){
										if (checked){
											Ext.Array.each(this.up().query('[hideIfShopAccessIsAllowedImmediately="yes"]'),function(name,index,object){
												name.hide();
												name.allowBlankBackup = name.allowBlank;
												name.allowBlank = true;
											});
										}else {
											Ext.Array.each(this.up().query('[hideIfShopAccessIsAllowedImmediately="yes"]'),function(name,index,object){
												name.show();
												name.allowBlank = name.allowBlankBackup;
											});
										}
									}
								}
							},
							{
								xtype: 'combobox',
								fieldLabel: 'Kundengruppe nach Registrierung',
								name: 'registergroup',
								hideIfRegisterNotAllowed: 'yes',
								allowBlank:true,
								displayField: 'description',
								valueField: 'groupkey',
								hideIfShopAccessIsAllowedImmediately: 'yes',
								queryMode: 'local',
								store: this.groupStore,
								helpText: 'Definieren Sie, welche Registrierungsseite / Kundengruppe verwendet werden soll - siehe Modul "Kundengruppen-Registrierung"',
								helpMarginTop: '-15px',
								helpMargin: '175px',
								listeners: {
									select: function (combo,record,options){
										if (record[0].data.groupkey == '{$customerGroup.groupkey}'){
											Ext.MessageBox.alert('Möglicherweise liegt ein Konfigurationsfehler vor!','Bitte stellen Sie sicher, dass die von Ihnen gewählte Konfiguration gewünscht ist. Falls Sie eine direkte Shop-Freischaltung nach Registrierung wünschen, aktivieren Sie die Checkbox "Shop-Zugriff nach Registrierung direkt freigeben"!');
										}
									}
								}
							},
							{
								xtype: 'textfield',
								fieldLabel: 'Template für Login-Seite',
								name: 'templatelogin',
								allowBlank:false,
								value: 'pslogin.tpl',
								helpText: 'Wählen Sie hier das Template, welches für die Login-Vorschaltseite verwendet werden soll.',
								helpMarginTop: '-15px',
								helpMargin: '200px',
								width: 450
							},
							{
								xtype: 'combobox',
								fieldLabel: 'Haupt-Template nach Login',
								name: 'templateafterlogin',
								allowBlank:true,
								displayField: 'name',
								valueField: 'value',
								queryMode: 'local',
								store: this.TemplateStore,
								helpText: 'Wählen Sie hier (falls gewünscht) ein abweichendes Template, welches nach Login mit dieser Kundengruppe verwendet wird! Diese Einstellung kann unabhängig von der Private-Shopping Funktionalität eingesetzt werden.',
								helpMarginTop: '-15px',
								helpMargin: '200px',
								width: 450
							}]
						})
						,
						{
							xtype: 'panel',
							bodyStyle: 'padding-left: 10px;padding-top: 5px;padding-right:10px;border: none; padding-bottom:15px',
							html: ''
						}
					]

				}{if !$customerGroup@last},{/if}
				{/foreach}
			]
		});
		this.callParent(arguments);
	}
});