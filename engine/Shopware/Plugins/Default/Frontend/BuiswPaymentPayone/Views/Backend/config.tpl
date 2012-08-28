{extends file="backend/index/parent.tpl"}

{block name="backend_index_body_inline"}
	{literal}
	<style>
		.bon_help {
			position:relative;
			z-index:1;
			cursor:help;
			color: #032C44;
			font-weight: bold;
		}

		.identitaet_help {
			font-weight: bold;
			cursor: help;
			width: 15px;
			position:absolute;
			margin-left: 250px;
			color: #032C44;
		}

		.status_help {
			font-weight: bold;
			color: #032C44;
		}
	</style>
	<script type="text/javascript">
	//<![CDATA[
		Ext.ns('Shopware.BuiswPayOne');

		Ext.onReady(function(){
			Ext.QuickTips.init();

			var mainHelp = new Ext.Panel ({
				html: '<span style="font: 12px tahoma,arial,helvetica,sans-serif;"><b>Zahlungsarten</b><br>Sie k&ouml;nnen f&uuml;r jede Zahlart einzeln konfigurieren, ob diese im Test- oder Livemodus abgewickelt werden soll. Wir empfehlen Ihnen nach der initialen Konfiguration sowie bei Konfigurations&auml;nderungen zun&auml;chst alle Zahlungsprozesse im Testmodus durchzuf&uuml;hren.</span>',
				colspan: 2
			});

			var mainConfig = {
				colspan: 2,
				items: {
					xtype: 'fieldset',
					collapsible: true,
					title: 'Identit&auml;t',
					autoHeight: true,
					defaultType: 'textfield',
					{/literal}
					items: [{
						fieldLabel: 'Merchant ID',
						name: 'merchant_id',
						value: '{$config.merchant_id}',
						clearCls:'x-form-clear-none',
						style: {
							float: 'left'
						}
					},{
						id: 'merchant_id_help',
						xtype: 'label',
						html: '<div id="merchant_id_help" class="identitaet_help" ext:qtip="Ihre PAYONE Merchant ID (PAYONE Kundennummer) finden Sie auf allen Abrechnungen von PAYONE sowie rechts oben im PAYONE Merchant Interface (PMI).">?</div>'
					},{
						fieldLabel: 'Portal ID',
						name: 'portal_id',
						value: '{$config.portal_id}',
						clearCls:'x-form-clear-none',
						style: {
							float: 'left'
						}
					},{
						id: 'portal_id_help',
						xtype: 'label',
						html: '<div id="merchant_id_help" style="margin-top: 20px" class="identitaet_help" ext:qtip="Bitte tragen Sie hier die ID des PAYONE Zahlungsportals ein, &uuml;ber welches die Zahlungen abgewickelt werden sollen.<br /> Die Portal-ID finden Sie unter http://www.payone.de > H&auml;ndler-Login unter dem Men&uuml;punkt Konfiguration > Zahlungsportale <br /><br />Alle relevanten Parameter zur Konfiguration erhalten Sie nach Auswahl von [editieren] unter dem Reiter [API-Parameter]">?</div>'
					},{
						fieldLabel: 'Portal Key',
						name: 'portal_key',
						value: '{$config.portal_key}',
						clearCls:'x-form-clear-none',
						style: {
							float: 'left'
						}
					},{
						id: 'portal_key_help',
						xtype: 'label',
						html: '<div id="merchant_id_help" style="margin-top: 40px" class="identitaet_help" ext:qtip="Bitte tragen Sie hier den Key zur Absicherung des Datenaustausches ein. Dieser kann bei der Konfiguration des PAYONE Zahlungsportals von Ihnen frei festgelegt werden.<br /> Die Konfiguration finden Sie unter http://www.payone.de > H&auml;ndler-Login unter dem Men&uuml;punkt Konfiguration > Zahlungsportale > [editieren] > Reiter [Erweitert] > Key<br /><br />Alle relevanten Parameter zur Konfiguration erhalten Sie nach Auswahl des Reiters [API-Parameter]">?</div>'
					},{
						fieldLabel: 'Sub-Account ID ',
						name: 'sub_account_id',
						value: '{$config.sub_account_id}',
						clearCls:'x-form-clear-none',
						style: {
							float: 'left'
						}
					},{
						id: 'sub_account_id_help',
						xtype: 'label',
						html: '<div id="merchant_id_help" style="margin-top: 60px" class="identitaet_help" ext:qtip="Bitte tragen Sie hier die ID des Sub-Accounts ein, &uuml;ber welchen die Zahlungen abgewickelt und zugeordnet werden sollen.<br /> Die ID finden Sie unter http://www.payone.de > H&auml;ndler-Login unter dem Men&uuml;punkt Konfiguration > Accounts<br /><br />Alle relevanten Parameter zur Konfiguration erhalten Sie unter http://www.payone.de > H&auml;ndler-Login unter dem Men&uuml;punkt Konfiguration > Zahlungsportale > [editieren] > Reiter [API-Parameter]">?</div>'
					}
					/* spaeter ...
					,{
						fieldLabel: 'Artikelliste versenden',
						name: 'send_cart',
						xtype: 'checkbox'
							{if $config.send_cart == 'on'}
						,checked: true
						{/if}
					}
					*/
					]
					{literal}
				}
			};

			function createAuthDropDown(key, value) {
				var dd = {
					xtype: 'combo',
					displayField:'name',
					name: key + '_authmethod',
					valueField:'id',
					value: value,
					store: new Ext.data.SimpleStore({
						fields:['id', 'name'],
						data: [['preauth', 'Vorauthorisierung'],['auth', 'Authorisierung']]
					}),
					triggerAction:'all',
					width: 125,
					mode:'local'
				};

				return dd;
			}

			function createAmpelDropDown(key, value) {
				var dd = {
					xtype: 'combo',
					displayField:'name',
					name: key + '_ampelwert',
					valueField:'id',
					value: value,
					store: new Ext.data.SimpleStore({
						fields:['id', 'name'],
						data: [['G', 'Gruen'],['Y', 'Gelb'],['R', 'Rot']]
					}),
					triggerAction:'all',
					width: 75,
					mode:'local'
				};

				return dd;
			}

			var creditCardHelp = new Ext.Panel ({
				html: '<span style="font:12px tahoma,arial,helvetica,sans-serif"><b>Aktive Kreditkartenbrands</b><br> Hier k&ouml;nnen  Sie die einzelnen Kreditkartenbrands f&uuml;r die Zahlart Kreditkarte aktivieren und konfigurieren.  Bitte beachten Sie, dass der jeweilige Kreditkartenbrand bei PAYONE beauftragt worden sein muss.</span>',
				colspan: 2
			});

			var creditCardConfig = {
				colspan: 2,
				items: {
					xtype: 'fieldset',
					collapsible: true,
					title: 'Kreditkarten',
					autoHeight: true,
					defaultType: 'radio',
					items: [new Ext.Panel({
						layout:'table',
						defaults: {
							bodyStyle: 'padding:0 10px 0;',
						},
						layoutConfig: {
							columns: 11
						},
						items: [
							{ html: '<label class="x-form-item x-form-item-label">Zahlart</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Aktiv</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Zuordnungen</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Position</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Live/Test</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Autorisierungs-Methode</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Aufschlag/Abschlag %</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Pauschaler Aufschlag</label>'},
							{ html: '<label class="x-form-item x-form-item-label">L&auml;nderspezifischer Aufschlag</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Ampelwert</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Score</label>'}
						]
					})]
				}
			};

			Array.prototype.foreach = function( callback ) {
				for( var k=0; k<this .length; k++ ) {
					callback( k, this[ k ] );
				}
			}
			{/literal}

			var target = creditCardConfig.items.items[0];

			{foreach item=c from=$creditcards}
				target.add ({ html: '<label class="x-form-item x-form-item-label">{$c.text}</label>'});
				target.add ({ xtype: 'checkbox', name: '{$c.key}_active', value: 1, checked: {$c.active}});
				target.add ({ xtype: 'button', text: 'L&auml;nder zuorden',
				handler: function() { assignCountries ("{$c.key}");}});
				target.add ({ xtype: 'textfield', name: "{$c.key}_sortorder", value: '{$c.sortorder}', autoCreate: { tag: "input", type: "text", size: "1"}});
				target.add ({ xtype: 'radiogroup', columns: 2, width:77, height: 42, items: [
					{ boxLabel: '<div style="clear:both;"></div><div style="color:#ff0000; display: block;float: left; height: 12px; font-size:10px;margin-top: -5px;">Live</div>', name: '{$c.key}_mode', inputValue: 'live' {if $c.live}, checked: true{/if}},
					{ boxLabel: '<div style="clear:both;"></div><div style="display: block;float: left; height: 12px; font-size:10px;margin-top: -5px;">Test</div>', name: '{$c.key}_mode', inputValue: 'test' {if $c.test} ,checked: true{/if}}
				]});
				target.add (createAuthDropDown ('{$c.key}', '{$c.authmethod}'));
				target.add ({ xtype: 'textfield',  name: "{$c.key}_cost_percent", value: '{$c.cost_percent}', autoCreate: { tag: "input", type: "text", size: "8"}});
				target.add ({ xtype: 'textfield',  name: "{$c.key}_cost_total", value: '{$c.cost_total}', autoCreate: { tag: "input", type: "text", size: "8"}});
				target.add ({ xtype: 'textfield',  name: "{$c.key}_cost_country", value: '{$c.cost_country}', autoCreate: { tag: "input", type: "text", size: "8"}});
				target.add (createAmpelDropDown ('{$c.key}', '{$c.ampelwert}'));
				target.add ({ xtype: 'textfield',  name: "{$c.key}_boniscore", value: '{$c.boniscore}', autoCreate: { tag: "input", type: "text", size: "8"}});
			{/foreach}

			{literal}
			var directDebitHelp = new Ext.Panel ({
				html: '<span style="font:12px tahoma,arial,helvetica,sans-serif"><b>Aktive Online-&Uuml;berweisungsarten</b><br> Hier k&ouml;nnen  Sie die einzelnen Online-&Uuml;berweisungsarten f&uuml;r die Zahlart Online-&Uuml;berweisung aktivieren und konfigurieren.  Bitte beachten Sie, dass die jeweilige Online-&Uuml;berweisungsart bei PAYONE beauftragt worden sein muss.</span>',
				colspan: 2
			});

			var directDebitConfig = {
				colspan: 2,
				items: {
					xtype: 'fieldset',
					collapsible: true,
					title: 'Online-&Uuml;berweisungsarten',
					autoHeight: true,
					defaultType: 'radio',
					items: [new Ext.Panel({
						layout:'table',
						defaults: {
							bodyStyle: 'padding:0 10px 0;',
						},
						layoutConfig: {
							columns: 11
						},
						items: [
							{ html: '<label class="x-form-item x-form-item-label">Zahlart</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Aktiv</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Zuordnungen</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Position</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Live/Test</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Autorisierungs-Methode</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Aufschlag/Abschlag %</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Pauschaler Aufschlag</label>'},
							{ html: '<label class="x-form-item x-form-item-label">L&auml;nderspezifischer Aufschlag</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Ampelwert</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Score</label>'}
						]
					})]
				}
			};
			{/literal}

			var target = directDebitConfig.items.items[0];

			{foreach item=c from=$directdebits}
				target.add ({ html: '<label class="x-form-item x-form-item-label">{$c.text}</label>'});
				target.add ({ xtype: 'checkbox', name: '{$c.key}_active', value: 1, checked: {$c.active}});
				target.add ({ xtype: 'button', text: 'L&auml;nder zuorden',
				handler: function() { assignCountries ("{$c.key}");}});
				target.add ({ xtype: 'textfield', name: "{$c.key}_sortorder", value: '{$c.sortorder}', autoCreate: { tag: "input", type: "text", size: "1"}});
				target.add ({ xtype: 'radiogroup', columns: 2, width:77, height: 42, items: [
					{ boxLabel: '<div style="clear:both;"></div><div style="color:#ff0000; display: block;float: left; height: 12px; font-size:10px;margin-top: -5px;">Live</div>', name: '{$c.key}_mode', inputValue: 'live' {if $c.live}, checked: true{/if}},
					{ boxLabel: '<div style="clear:both;"></div><div style="display: block;float: left; height: 12px; font-size:10px;margin-top: -5px;">Test</div>', name: '{$c.key}_mode', inputValue: 'test' {if $c.test} ,checked: true{/if}}
				]});
				target.add (createAuthDropDown ('{$c.key}', '{$c.authmethod}'));
				target.add ({ xtype: 'textfield',  name: "{$c.key}_cost_percent", value: '{$c.cost_percent}', autoCreate: { tag: "input", type: "text", size: "8"}});
				target.add ({ xtype: 'textfield',  name: "{$c.key}_cost_total", value: '{$c.cost_total}', autoCreate: { tag: "input", type: "text", size: "8"}});
				target.add ({ xtype: 'textfield',  name: "{$c.key}_cost_country", value: '{$c.cost_country}', autoCreate: { tag: "input", type: "text", size: "8"}});
				target.add (createAmpelDropDown ('{$c.key}', '{$c.ampelwert}'));
				target.add ({ xtype: 'textfield',  name: "{$c.key}_boniscore", value: '{$c.boniscore}', autoCreate: { tag: "input", type: "text", size: "8"}});
			{/foreach}

			{literal}
			var otherConfig = {
				colspan: 2,
				items: {
					xtype: 'fieldset',
					collapsible: true,
					title: 'Andere Zahlungsweisen',
					autoHeight: true,
					autoWidth: true,
					defaultType: 'radio',
					items: [new Ext.Panel({
						layout:'table',
						defaults: {
							bodyStyle: 'padding:0 10px 0;'
						},
						layoutConfig: {
							columns: 11
						},
						items: [
							{ html: '<label class="x-form-item x-form-item-label">Zahlart</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Aktiv</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Zuordnungen</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Position</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Live/Test</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Autorisierungs-Methode</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Aufschlag/Abschlag %</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Pauschaler Aufschlag</label>'},
							{ html: '<label class="x-form-item x-form-item-label">L&auml;nderspezifischer Aufschlag</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Ampelwert</label>'},
							{ html: '<label class="x-form-item x-form-item-label">Score</label>'}
						]
					})]
				}
			};
			{/literal}

			var target = otherConfig.items.items[0];
			{foreach item=c from=$otherpayments}
				target.add ({ html: '<label class="x-form-item x-form-item-label">{$c.text}</label>'});
				target.add ({ xtype: 'checkbox', name: '{$c.key}_active', value: 1, checked: {$c.active}});
				target.add ({ xtype: 'button', text: 'L&auml;nder zuorden',
				handler: function() { assignCountries ("{$c.key}");}});
				target.add ({ xtype: 'textfield', name: "{$c.key}_sortorder", value: '{$c.sortorder}', autoCreate: { tag: "input", type: "text", size: "1"}});
				target.add ({ xtype: 'radiogroup', columns: 2, width:77, height: 42, items: [
					{ boxLabel: '<div style="clear:both;"></div><div style="color:#ff0000; display: block;float: left; height: 12px; font-size:10px;margin-top: -5px;">Live</div>', name: '{$c.key}_mode', inputValue: 'live' {if $c.live}, checked: true{/if}},
					{ boxLabel: '<div style="clear:both;"></div><div style="display: block;float: left; height: 12px; font-size:10px;margin-top: -5px;">Test</div>', name: '{$c.key}_mode', inputValue: 'test' {if $c.test} ,checked: true{/if}}
				]});
				target.add (createAuthDropDown ('{$c.key}', '{$c.authmethod}'));
				target.add ({ xtype: 'textfield',  name: "{$c.key}_cost_percent", value: '{$c.cost_percent}', autoCreate: { tag: "input", type: "text", size: "8"}});
				target.add ({ xtype: 'textfield',  name: "{$c.key}_cost_total", value: '{$c.cost_total}', autoCreate: { tag: "input", type: "text", size: "8"}});
				target.add ({ xtype: 'textfield',  name: "{$c.key}_cost_country", value: '{$c.cost_country}', autoCreate: { tag: "input", type: "text", size: "8"}});
				target.add (createAmpelDropDown ('{$c.key}', '{$c.ampelwert}'));
				target.add ({ xtype: 'textfield',  name: "{$c.key}_boniscore", value: '{$c.boniscore}', autoCreate: { tag: "input", type: "text", size: "8"}});
			{/foreach}

			{literal}
			var theMegaSaveButton = {
				id:'theMegaSaveButton',
				region: 'south',
				layout: 'fit',
				height: 50,
				minHeight: 50,
				split: true,
				items: [{
					id:'theMegaSaveButton',
					xtype:'button',
					text: 'Speichern',
					cls: 'savebutton',
					iconCls:'disk',
					listeners:{
						'click': function(){
							var form = Shopware.BuiswPayOne.PanelCenter;

							form.getForm().submit({
								url: 'saveConfig', // {url action=saveConfig}',
								success: function(f,a){
									//parent.parent.Growl ("Einstellungen wurden &uuml;bernommen!");
                                    alert("Einstellungen wurden übernommen!")
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
			};
			{/literal}

			Shopware.BuiswPayOne.mappings = {$paystates};

			var statusMapping = {
				colspan: 2,
				items: {
					xtype: 'fieldset',
					collapsible: true,
					title: 'Status Mappings',
					autoHeight: true,
					defaultType: 'combo',
					items: [{
						id: 'paystatus_approved_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="paystatus_approved_help" class="status_help" ext:qtip="Dieser Zahlstatus wird vergeben wenn PAYONE die Zahlung genehmigt hat." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Zahlstatus -<br> Approved',
						name: 'paystatus_approved',
						displayField:'name',
						valueField:'id',
						value: {$paystatus_approved},
						store: new Ext.data.SimpleStore({
							fields:['id', 'name'],
							data:Shopware.BuiswPayOne.mappings
						}),
						triggerAction:'all',
						mode:'local',
						style: {
							float: 'left'
						}
					},{
						id: 'paystatus_appointed_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="paystatus_appointed_help" class="status_help" ext:qtip="Dieser ZahlStatus wird vergeben wenn die Zahlung erfolgreich im System von PAYONE eingegangen ist." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Zahlstatus -<br> Appointed',
						name: 'paystatus_appointed',
						displayField:'name',
						valueField:'id',
						value: {$paystatus_appointed},
						store: new Ext.data.SimpleStore({
							fields:['id', 'name'],
							data:Shopware.BuiswPayOne.mappings
						}),
						triggerAction:'all',
						mode:'local',
						style: {
							float: 'left'
						}
					},{
						id: 'paystatus_captured_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="paystatus_captured_help" class="status_help" ext:qtip="Dieser Zahlstatus wird vergeben wenn die Zahlung erfolgreich von PAYONE vom Kunden eingezogen wurde." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Zahlstatus -<br> Captured',
						name: 'paystatus_captured',
						displayField:'name',
						valueField:'id',
						value: {$paystatus_capture},
						store: new Ext.data.SimpleStore({
							fields:['id', 'name'],
							data:Shopware.BuiswPayOne.mappings
						}),
						triggerAction:'all',
						mode:'local',
						style: {
							float: 'left'
						}
					},{
						id: 'paystatus_paid_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="paystatus_paid_help" class="status_help" ext:qtip="Dieser Zahlstatus wird vergeben wenn die Zahlung vom Kunden bei PAYONE eingegangen ist." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Zahlstatus -<br> Paid',
						name: 'paystatus_paid',
						displayField:'name',
						valueField:'id',
						value: {$paystatus_paid},
						store: new Ext.data.SimpleStore({
							fields:['id', 'name'],
							data:Shopware.BuiswPayOne.mappings
						}),
						triggerAction:'all',
						mode:'local',
						style: {
							float: 'left'
						}
					},{
						id: 'paystatus_underpaid_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="paystatus_underpaid_help" class="status_help" ext:qtip="Dieser Zahlstatus wird vergeben wenn der vom Kunden gezahlte Betrag an PAYONE noch nicht vollst&auml;ndig beglichen wurde." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Zahlstatus -<br> Underpaid',
						name: 'paystatus_underpaid',
						displayField:'name',
						valueField:'id',
						value: {$paystatus_underpaid},
						store: new Ext.data.SimpleStore({
							fields:['id', 'name'],
							data:Shopware.BuiswPayOne.mappings
						}),
						triggerAction:'all',
						mode:'local',
						style: {
							float: 'left'
						}
					},{
						id: 'paystatus_cancelation_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="paystatus_cancelation_help" class="status_help" ext:qtip="Dieser Zahlstatus wird vergeben wenn die Zahlung annulliert wurde." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Zahlstatus -<br> Cancelation',
						name: 'paystatus_cancelation',
						displayField:'name',
						valueField:'id',
						value: {$paystatus_cancelation},
						store: new Ext.data.SimpleStore({
							fields:['id', 'name'],
							data:Shopware.BuiswPayOne.mappings
						}),
						triggerAction:'all',
						mode:'local',
						style: {
							float: 'left'
						}
					},{
						id: 'paystatus_refund_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="paystatus_refund_help" class="status_help" ext:qtip="Dieser Zahlstatus wird vergeben wenn die Zahlung zur&uuml;ckerstattet wurde." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Zahlstatus -<br> Refund',
						name: 'paystatus_refund',
						displayField:'name',
						valueField:'id',
						value: {$paystatus_refund},
						store: new Ext.data.SimpleStore({
							fields:['id', 'name'],
							data:Shopware.BuiswPayOne.mappings
						}),
						triggerAction:'all',
						mode:'local',
						style: {
							float: 'left'
						}
					},{
						id: 'paystatus_debit_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="paystatus_debit_help" class="status_help" ext:qtip="Dieser Zahlstatus wird vergeben wenn eine vorautorisierte Zahlung eingefordert wurde." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Zahlstatus -<br> Debit',
						name: 'paystatus_debit',
						displayField:'name',
						valueField:'id',
						value: {$paystatus_debit},
						store: new Ext.data.SimpleStore({
							fields:['id', 'name'],
							data:Shopware.BuiswPayOne.mappings
						}),
						triggerAction:'all',
						mode:'local',
						style: {
							float: 'left'
						}
					},{
						id: 'paystatus_reminder_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="paystatus_reminder_help" class="status_help" ext:qtip="Dieser Zahlstatus wird vergeben wenn der Kunde angemahnt worden ist." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Zahlstatus -<br> Reminder',
						name: 'paystatus_reminder',
						displayField:'name',
						valueField:'id',
						value: {$paystatus_reminder},
						store: new Ext.data.SimpleStore({
							fields:['id', 'name'],
							data:Shopware.BuiswPayOne.mappings
						}),
						triggerAction:'all',
						mode:'local',
						style: {
							float: 'left'
						}
					}
					/*
					{
						fieldLabel: 'Zahlstatus  - N+1',
						name: 's_2',
						displayField:'name',
						valueField:'id',
						value: 13,
						store: new Ext.data.SimpleStore({
							fields:['id', 'name']
							,data:Shopware.BuiswPayOne.mappings
						})
						,triggerAction:'all'
						,mode:'local'
					}
					*/
					]
				}
			};

			{literal}
			var boniConfigHelp = new Ext.Panel ({
				html: '<span style="font:12px tahoma,arial,helvetica,sans-serif"><b>Bitte beachten Sie, dass Sie die nachfolgenden Optionen nur dann nutzen k&ouml;nnen, wenn Sie das Modul Protect von PAYONE beauftragt haben. Die Nutzung der Bonit&auml;tspr&uuml;fung und der Adresspr&uuml;fung zieht variable Kosten pro Vorgang nach sich, die Sie Ihrem Vertrag entnehmen k&ouml;nnen.</b><br><br> Bitte nehmen Sie die Einstellungen f&uuml;r die Bonit&auml;tspr&uuml;fung mit Bedacht vor. Die Bonit&auml;tspr&uuml;fung wird nach Eingabe der Personendaten durchgef&uuml;hrt und beeinflusst die Zahlungsarten, die Ihren Kunden im Checkout-Prozess angeboten werden. Die Bonit&auml;tspr&uuml;fung sollte lediglich bei Zahlungsarten eingesetzt werden, die ein Zahlungsausfallrisiko f&uuml;r Sie nach sich ziehen (z.B. offene Rechnung oder Lastschrift). Sie konfigurieren dies &uuml;ber die Einstellung "Ampelwert / Score" in der Konfiguration der jeweiligen Zahlart. Sie sollten in Ihrem Shop au&szlig;erdem in geeigneter Weise darauf hinweisen, dass Sie Bonit&auml;tspr&uuml;fungen &uuml;ber die InfoScore Consumer Data GmbH durchf&uuml;hren.</span>',
				colspan: 2
			});
			{/literal}

			var boniConfig = {
				colspan: 2,
				items: {
					xtype: 'fieldset',
					collapsible: true,
					title: 'Bonit&auml;ts- und Adresspr&uuml;fung',
					autoHeight: true,

					items: [{
						fieldLabel:'Betriebsmodus',
						boxLabel: 'Live',
						name: 'bonitaets_mode',
						inputValue: 'live',
						xtype:'radio'
						{if $config.bonitaets_mode == "live"}, checked: true{/if}
					},{
						boxLabel: 'Test',
						name: 'bonitaets_mode',
						inputValue: 'test',
						xtype:'radio'
						{if $config.bonitaets_mode == "test"}, checked: true{/if}
					},{
						id: 'bonitaets_type_no_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_no_help" class="bon_help" ext:qtip="Deaktivierung der Bonit&auml;tspr&uuml;fung">?</div>'
					},{
						fieldLabel:'Bonit&auml;tspr&uuml;fung',
						boxLabel: 'Keine Bonit&auml;tspr&uuml;fung durchf&uuml;hren',
						name: 'bonitaets_type',
						inputValue: 'NO',
						xtype:'radio',
						height: '40px',
						width: 200,
						style: {
							float: 'left',
							width: '100'
						}
						{if $config.bonitaets_type == "NO"}, checked: true{/if}
					},{
						id: 'bonitaets_type_ih_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ih_help" class="bon_help" ext:qtip="Pr&uuml;fung auf so genannte harte Negativmerkmale (z.B. Verbraucherinsolvenzverfahren, Haftbefehl zur Eidesstattliche Versicherung oder Erzwingung Abgabe der Eidesstattlichen Versicherung). Die Bonit&auml;tspr&uuml;fung unterst&uuml;tzt ausschlie&szlig;lich die Pr&uuml;fung von K&auml;ufern aus Deutschland." style="cursor:help;">?</div>'
					},{
						boxLabel: 'Infoscore (Harte Merkmale)',
						name: 'bonitaets_type',
						inputValue: 'IH',
						xtype:'radio',
						style: {
							float: 'left'
						}
						{if $config.bonitaets_type == "IH"}, checked: true{/if}
					},{
						id: 'bonitaets_type_ia_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ia_help" class="bon_help" ext:qtip="Pr&uuml;fung auf so genannte harte Negativmerkmale (z.B. Verbraucherinsolvenzverfahren, Haftbefehl zur Eidesstattliche Versicherung oder Erzwingung Abgabe der Eidesstattlichen Versicherung), mittlere Negativmerkmale (z.B. Mahnbescheid, Vollstreckungsbescheid oder Zwangsvollstreckung) und weiche Negativmerkmale (z.B. Inkasso-Mahnverfahren eingeleitet, Fortlauf des au&szlig;ergerichtlichen Inkasso-Mahnverfahrens nach Teilzahlung, Einstellung des au&szlig;ergerichtlichen Inkasso-Mahnverfahrens wegen Aussichtslosigkeit). Die Bonit&auml;tspr&uuml;fung unterst&uuml;tzt ausschlie&szlig;lich die Pr&uuml;fung von K&auml;ufern aus Deutschland." style="cursor:help;">?</div>'
					},{
						boxLabel: 'Infoscore (Alle Merkmale)',
						name: 'bonitaets_type',
						inputValue: 'IA',
						xtype:'radio',
						style: {
							float: 'left'
						}
						{if $config.bonitaets_type == "IA"}, checked: true{/if}
					},{
						id: 'bonitaets_type_ib_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ib_help" class="bon_help" ext:qtip="Pr&uuml;fung auf so genannte harte Negativmerkmale (z.B. Verbraucherinsolvenzverfahren, Haftbefehl zur Eidesstattliche Versicherung oder Erzwingung Abgabe der Eidesstattlichen Versicherung), mittlere Negativmerkmale (z.B. Mahnbescheid, Vollstreckungsbescheid oder Zwangsvollstreckung) und weiche Negativmerkmale (z.B. Inkasso-Mahnverfahren eingeleitet, Fortlauf des au&szlig;ergerichtlichen Inkasso-Mahnverfahrens nach Teilzahlung, Einstellung des au&szlig;ergerichtlichen Inkasso-Mahnverfahrens wegen Aussichtslosigkeit). Die Bonit&auml;tspr&uuml;fung unterst&uuml;tzt ausschlie&szlig;lich die Pr&uuml;fung von K&auml;ufern aus Deutschland.<br /><br />Der BoniScore ist ein Scorewert und erm&ouml;glicht eine h&ouml;here Trennsch&auml;rfe bei vorliegenden Negativmerkmalen." style="cursor:help;">?</div>'
					},{
						boxLabel: 'Infoscore (Alle Merkmale + Boniscore)',
						name: 'bonitaets_type',
						inputValue: 'IB',
						xtype:'radio',
						height: '40px',
						style: {
							float: 'left'
						}
						{if $config.bonitaets_type == "IB"}, checked: true{/if}
					},{
						id: 'bonitaets_lifetime_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ib_help" class="bon_help" ext:qtip="Anzahl in Tagen, nach der eine neue Bonit&auml;tspr&uuml;fung durchgef&uuml;hrt wird.<br /><br />Bitte beachten Sie die Bestimmungen des BDSG und der Vertragsbedingungen bzgl. der Speicherung und der Lebensdauer der Bonit&auml;tspr&uuml;fungen. Es wird empfohlen, eine Lebensdauer von 1 Tag zu konfigurieren." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Lebensdauer Bonit&auml;tspr&uuml;fung in Tagen',
						name: 'bonitaets_lifetime',
						xtype:'textfield',
						value: '{$config.bonitaets_lifetime}'
					},{
						id: 'bonitaets_minbasketvalue_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ib_help" class="bon_help" ext:qtip="Bonit&auml;tspr&uuml;fung wird nur ausgef&uuml;hrt wenn der Warenwert h&ouml;her als der hier konfigurierte Wert ist.<br /><br />Wenn die Bonit&auml;tspr&uuml;fung immer durchgef&uuml;hrt werden soll, tragen Sie eine 0 ein. " style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Bonit&auml;tspr&uuml;fung ab Warenwert',
						name: 'bonitaets_minbasketvalue',
						xtype:'textfield',
						value: '{$config.bonitaets_minbasketvalue}'
					},{
						id: 'bonitaets_defaultindex_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ib_help" class="bon_help" ext:qtip="Diesen Bonit&auml;ts-Index erh&auml;lt der Kunde in bestimmten Fehlerf&auml;llen. (Nicht-Erreichbarkeit des Services z.B.)<br /> Zweck: Wenn der Kunde nicht gepr&uuml;ft werden kann, ist dies der Bonit&auml;ts-Index der bis zur ersten tats&auml;chlichen Pr&uuml;fung ber&uuml;cksichtigt wird." style="cursor:help;">?</div>'
					},{
						fieldLabel: 'Standard Bonit&auml;ts-Index',
						name: 'bonitaets_defaultindex',
						xtype:'textfield',
						value: '{$config.bonitaets_defaultindex}'
					},{
						id: 'addresscheck_type_no_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ib_help" class="bon_help" ext:qtip="Deaktivierung der Adresspr&uuml;fung" style="cursor:help;">?</div>'
					},{
						fieldLabel:'Adresspr&uuml;fung',
						boxLabel: 'Keine Adresspr&uuml;fung durchf&uuml;hren',
						name: 'addresscheck_type',
						inputValue: 'NO',
						xtype:'radio'
						{if $config.addresscheck_type == "NO"}, checked: true{/if}
					},{
						id: 'addresscheck_type_ba_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ib_help" class="bon_help" ext:qtip="Pr&uuml;fung der Adresse auf Existenz sowie Erg&auml;nzung und Korrektur der Adresse (M&ouml;glich f&uuml;r Adressen aus Deutschland, &Ouml;sterreich, Schweiz, Niederlande, Belgien, Luxemburg, Frankreich, Italien, Spanien, Portugal, D&auml;nemark, Schweden, Finnland, NorwegenPolen, Slowakei, Tschechien, Ungarn, USA, Kanada)" style="cursor:help;">?</div>'
					},{
						boxLabel: 'AdressCheck Basic',
						name: 'addresscheck_type',
						inputValue: 'BA',
						xtype:'radio'
						{if $config.addresscheck_type == "BA"}, checked: true{/if}
					},{
						id: 'addresscheck_type_pe_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ib_help" class="bon_help" ext:qtip="Pr&uuml;fung ob die Person unter der angegebenen der Adresse bekannt ist, Pr&uuml;fung der Adresse auf Existenz sowie Erg&auml;nzung und Korrektur der Adresse (nur Deutschland)" style="cursor:help;">?</div>'
					},{
						boxLabel: 'AdressCheck Person',
						name: 'addresscheck_type',
						inputValue: 'PE',
						xtype:'radio'
						{if $config.addresscheck_type == "PE"}, checked: true{/if}
					},{
						id: 'applynewaddress_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ib_help" class="bon_help" ext:qtip="&Uuml;bernahme der jeweils durch die Adresspr&uuml;fung korrigierten Adresse anstatt der eingegebenen Adresse in Ihren Shop." style="cursor:help;">?</div>'
					},{
						boxLabel: 'Korrigierte Adressen &uuml;bernehmen',
						name: 'applynewaddress',
						inputValue: 'true',
						xtype:'checkbox'
						{if $config.applynewaddress == "true"}, checked: true{/if}
					},{
						id: 'onaddresscheckerrorredirect_help',
						xtype: 'label',
						style: {
							float: 'right',
							margin: '0% 70% 0% 0%'
						},
						html: '<div id="bonitaets_type_ib_help" class="bon_help" ext:qtip="Wenn bei der Adresspr&uuml;fung keine g&uuml;ltige &Uuml;bereinstimmung gefunden wurde, so wird der Kunde zum Adressformular weitergeleitet, um eine g&uuml;ltige Adresse einzugeben." style="cursor:help;">?</div>'
					},{
						boxLabel: 'Wenn Adresse postalisch falsch wird Benutzer zum Benutzerformular zur&uuml;ck geschickt ',
						name: 'onaddresscheckerrorredirect',
						inputValue: 'true',
						xtype:'checkbox',
						height: 60
						{if $config.onaddresscheckerrorredirect == "true"}, checked: true{/if}
					}]
				}
			}

			{literal}
			var PanelCenter = new Ext.FormPanel({
				title: 'Konfiguration',
				region:'center',
				layout:'table',
				autoLoad: false,
				autoScroll: true,
				defaults: {
					bodyStyle:'padding:5px',
					border: false
				},
				layoutConfig: {
					columns: 2
				},
				items: [
					mainConfig, 
                    mainHelp, 
                    otherConfig ,
                    creditCardHelp, 
                    creditCardConfig, 
                    directDebitHelp, 
                    directDebitConfig, 
                    statusMapping, 
                    boniConfigHelp, 
                    boniConfig
				],
				width: 213,
				loadConfig: function(type, text) {
					this.setTitle ('Konfiguration ' + text);
					/*
					Ext.Ajax.request({
						url: 'configForm',
						waitMsg: 'Laden',
						success: function(d) {Shopware.BuiswPayOne.PanelCenter.body.update (d.responseText);},

						failure: function() {},
						params: { type: type }
					});
					*/
				}
			});

			function assignCountries (key) {
				parent.parent.window.openAction('BuiswPaymentPayone', 'assignCountriesStoresEct', { key: key});
			}

			Shopware.BuiswPayOne.PanelCenter = PanelCenter;

			new Ext.Viewport ({
				layout: 'border',
				items: [/*TreeLeft,*/ PanelCenter, theMegaSaveButton]
			});
		});
	//]]>
	</script>
	{/literal}
{/block}