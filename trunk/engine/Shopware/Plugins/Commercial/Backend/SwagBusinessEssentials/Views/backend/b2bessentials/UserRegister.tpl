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
				shadow: false,
				dismissDelay: 5000 // Hide after 10 seconds hover
			});
        }
        this.callOverridden();
  }
});
Ext.define('Shopware.B2B.UserRegister',
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
		 {foreach from=$customerGroups item=customerGroup}
				{ "description":"{$customerGroup.description}","groupkey":"{$customerGroup.groupkey}"}{if !$customerGroup@last},{/if}
		 {/foreach}
		 ];
		 this.groupStore = Ext.create('Ext.data.Store', {
				model: 'Customergroups',
				data: this.customerGroups
		 });

		Ext.define('eMailTemplates', {
				extend: 'Ext.data.Model',
				fields: [
					{ type: 'string', name: 'id'},
					{ type: 'string', name: 'name'},
					{ type: 'string', name: 'description'}
				]
		});

		this.emailStore = Ext.create('Ext.data.Store',
		{
			model: 'eMailTemplates',
			autoLoad: false,
			proxy: {
				// load using HTTP
				type: 'ajax',
				url: '{url controller=BusinessEssentials action=getMailTemplates}',
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
			title: 'Kundengruppen Registrierung',
			id: 'UserRegister',
			autoScroll: true,
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
					html: '<strong style="font-weight:bold">Eigene Registrierungsseiten</strong><br />Mit diesem Modul erstellen Sie unabhängige Registrierungsseiten für einzelne Kundengruppen. Diese können Sie optional nach Registrierung im Backend freischalten.',
					bodyStyle: 'padding-left: 10px;padding-top: 5px;padding-right:10px;border: none; padding-bottom:15px'
				},
				{foreach from=$customerGroups item=customerGroup}
				{

					xtype: 'fieldset',
					title: 'Kundengruppe "{$customerGroup.description} - {$customerGroup.groupkey}"',
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
						{if $customerGroup.groupkey == "EK"}
						{
							xtype: 'panel',
							height:50,
                            bodyStyle: 'padding-left: 10px;padding-top: 5px;padding-right:10px;border: none; padding-bottom:15px',
							html: '<strong style="font-weight:bold">Für die Standardkundengruppe (Guest / EK) ist keine weitergehende Registrierungskonfiguration notwendig!</strong>'
						}
						{else}
						Ext.create('Ext.form.Panel', {
							url:'save-form.php',
							loadForm: function(form){
								form.load({ url: '{url action=loadRegisterConfig}/customergroup/{$customerGroup.groupkey}', success: function(form,action){

										if (!action.result.data.allowregister || action.result.data.allowregister == "0"){
											// Hiding underlying fields
											Ext.Array.each(form.owner.query('[hideIfGroupInactive="yes"]'),function(name,index,object){
												name.allowBlankBackup = name.allowBlank;
												name.allowBlank = true;
												name.hide();
											});
											Ext.getCmp('infoPanel{$customerGroup.groupkey}').hide();
										}else {
											Ext.getCmp('templateRegister{$customerGroup.groupkey}').allowBlank = true;
										}
										if (!action.result.data.requireunlock || action.result.data.requireunlock == "0"){
											Ext.Array.each(form.owner.query('[hideIfGroupRequiresUnlock="yes"]'),function(name,index,object){
												name.allowBlankBackup = name.allowBlank;
												name.allowBlank = true;
												name.hide();
										});
										}

									}}
									);
							},
							listeners: {
								'afterrender': function(form){
									this.loadForm(form);
								}
							},
							frame:true,
							title: 'Konfiguration',
							bodyStyle:'padding:5px 5px 0',
							groupStore: this.groupStore,
							fieldDefaults: {
								msgTarget: 'side',
								labelWidth: 250
							},
							defaultType: 'textfield',
							dockedItems: [{
								dock: 'bottom',
								xtype: 'container',
								items: [{
									xtype: 'button',
									text: 'Speichern',
									scale: 'large',
									padding: '0 20',
									handler: function(e){
										this.up('form').getForm().submit(
										{
											url: '{url action=saveRegisterConfig}/customergroup/{$customerGroup.groupkey}',
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
								}]
							}],
							items: [
							{
								xtype: 'checkbox',
								fieldLabel: 'Registrierung für diese Kundengruppe erlauben',
								name: 'allowregister',
								width: '100',
								allowBlank:false,
								helpText: 'Aktivieren Sie diese Checkbox, um eine separat aufrufbare Registrierungsseite für diese Kundengruppe freizugeben!',
								helpMargin: '5px',
								helpMarginTop: '-15px',
								listeners: {
									change: function (a,checked,c){
										if (checked){
											Ext.Array.each(this.up().query('[hideIfGroupInactive="yes"]'),function(name,index,object){
												name.show();
												name.allowBlank = name.allowBlankBackup;
											});
											Ext.getCmp('infoPanel{$customerGroup.groupkey}').show();
											Ext.getCmp('templateRegister{$customerGroup.groupkey}').allowBlank = true;
										}else {
											Ext.Array.each(this.up().query('[hideIfGroupInactive="yes"]'),function(name,index,object){
												name.hide();
												name.allowBlankBackup = name.allowBlank;
												name.allowBlank = true;
											});
											Ext.getCmp('infoPanel{$customerGroup.groupkey}').hide();
										}
									}
								}
							},{
								xtype: 'checkbox',
								fieldLabel: 'Freischaltung im Backend erforderlich',
								name: 'requireunlock',
								allowBlank:false,
								hideIfGroupInactive: 'yes',
								helpText: 'Wünschen Sie eine manuelle Freischaltung / Zuordnung der Kunden zu dieser Kundengruppe?',
								helpMargin: '5px',
								helpMarginTop: '-15px',
								listeners: {
									change: function (a,checked,c){
										if (checked){
											Ext.Array.each(this.up().query('[hideIfGroupRequiresUnlock="yes"]'),function(name,index,object){
												name.show();
												name.allowBlank = name.allowBlankBackup;
											});
										}else {
											Ext.Array.each(this.up().query('[hideIfGroupRequiresUnlock="yes"]'),function(name,index,object){
												name.hide();
												name.allowBlankBackup = name.allowBlank;
												name.allowBlank = true;
											});
										}
									}
								}
							},
							{
								xtype: 'combobox',
								fieldLabel: 'Zugewiesene Kundengruppe vor Freischaltung',
								name: 'assigngroupbeforeunlock',
								hideIfGroupInactive: 'yes',
								hideIfGroupRequiresUnlock: 'yes',
								allowBlank:false,
								displayField: 'description',
								valueField: 'groupkey',
								queryMode: 'local',
								store: this.groupStore,
								helpText: 'In welche Kundengruppe soll dieser Kunde nach Registrierung (vor Freischaltung) eingeordnet werden?<br />Standardmäßig ist der Kunde bis zur Freischaltung in der Kundengruppe "EK / Shopkunden"',
								helpMarginTop: '-15px',
								helpMargin: '175px',
								listeners: {
									select: function (combo,record,options){
										if (record[0].data.groupkey == '{$customerGroup.groupkey}'){
											Ext.MessageBox.alert('Möglicherweise liegt ein Konfigurationsfehler vor!','Bitte stellen Sie sicher, dass die von Ihnen gewählte Konfiguration gewünscht ist. Mit dieser Konfiguration wird der Kunde nach Registrierung unmittelbar der Kundengruppe zugeordnet, für die er eigentlich freigeschaltet werden soll. Falls Sie eine direkte Freischaltung wünschen, deaktivieren Sie die Checkbox "Freischaltung im Backend erforderlich" ');
										}
									}
								}
							},
							{
								xtype: 'combobox',
								fieldLabel: 'eMail-Template für Freischaltung',
								name: 'emailtemplateallow',
								hideIfGroupInactive: 'yes',
								allowBlank:false,
								displayField: 'name',
								hideIfGroupRequiresUnlock: 'yes',
								valueField: 'name',
								queryMode: 'remote',
								grow: true,
								store: this.emailStore,
								helpText: 'Wählen Sie hier das eMail-Template, dass im Falle einer Freischaltung verwendet werden soll. Die Templates verwalten Sie unter Einstellungen > eMail-Vorlagen.',
								helpMarginTop: '-15px',
								helpMargin: '200px',
								width: 450
							},
							{
								xtype: 'combobox',
								fieldLabel: 'eMail-Template für Ablehnung',
								name: 'emailtemplatedeny',
								allowBlank:false,
								hideIfGroupInactive: 'yes',
								displayField: 'name',
								grow: true,
								valueField: 'name',
								hideIfGroupRequiresUnlock: 'yes',
								queryMode: 'remote',
								store: this.emailStore,
								helpText: 'Wählen Sie hier das eMail-Template, dass im Falle einer Ablehnung verwendet werden soll. Die Templates verwalten Sie unter Einstellungen > eMail-Vorlagen."',
								helpMarginTop: '-15px',
								helpMargin: '200px',
								width: 450
							},
							{
								fieldLabel: 'Template für Registrierung',
								name: 'registertemplate',
								hideIfGroupInactive: 'yes',
								id: 'templateRegister{$customerGroup.groupkey}',
								width: 450,
								helpText: 'Wählen Sie hier ein eigenes Template für die Registrierungsseite (optional) - standardmäßig wird die normale Register-Seite angezeigt! Das Template muss im register Verzeichnis liegen! Beispiel index_merchants.tpl!',
								helpMargin: '200px',
								helpMarginTop: '-15px',
								value: '',
								allowBlank: true,
								listeners: {
									change: function (combo,record,options){
										combo.allowBlank = true;
									},
									focus: function(component){
										component.allowBlank = true;
									}
								}
							}]
						})
						,
						{
							id: 'infoPanel{$customerGroup.groupkey}',
							xtype: 'panel',
                            height: 50,
							bodyStyle: 'padding-left: 10px;padding-top: 5px;padding-right:10px;border: none; padding-bottom:15px',
							html: '<strong style="font-weight:bold">Link zur Registrierung:</strong> <input type="text" value="shopware.php?sViewport=register&sValidation={$customerGroup.groupkey}" readonly="readonly" style="width: 290px" /><br />Sie können diesen Link zum Beispiel als Shopseite (Inhalte > Shopseiten) in das Frontend integrieren '
						}
						{/if}
					]

				}{if !$customerGroup@last},{/if}
				{/foreach}
			]
		});
		this.callParent(arguments);
	}
});
