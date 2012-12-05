{* dieses template wird in 2 verschiedenen kontexten heraus aufgerufen: *}
{* frontend/checkout/confirm_payment.tpl und frontend/register/payment_fieldset.tpl *}
{* leider sind da jeweils andere variablen gesetzt *}
{* versuch 1 *}
{if !$form_data && $payment_mean && $sFormData}
	{assign var="form_data" value=$sFormData}
	{assign var="autoSubmit" value="1"}
{/if}
<div class="payone">
	{literal}
		<script type="text/javascript" src="https://secure.pay1.de/client-api/js/ajax.js"></script>
		<script type="text/javascript">
			var payone_payId = {/literal}{$form_data.payone_payId}{literal};
			var formular_may_submit = false;
			var data_sent = null;

			function selectPayone(b) {
				var id = 'payment_mean';
				id += payone_payId;
				var elem = document.getElementById (id);

				if (elem) {
					elem.checked = true;
				}

				if(b.value == "creditcard" && {/literal}{$autoSubmit}{literal}+"A" == "1A") {
					{/literal}
						var url = "{url sViewport='BuiswPaymentPayone' action='appendHash' forceSecure=1}";
						jQuery.ajax({
							type: 'POST',
							url: url,
							data: { cardtype: document.getElementsByName("payonesubpay_creditcard_card")[0].value},
							dataType: 'json',
							async: false,
							success: function(r) {
								data_sent = r;
								r.cardpan = document.getElementsByName("payonesubpay_creditcard_number")[0].value;
								r.cardtype  = document.getElementsByName("payonesubpay_creditcard_card")[0].value;
								r.cardexpiredate = '' + document.getElementsByName("payonesubpay_creditcard_validuntilyear")[0].value + document.getElementsByName("payonesubpay_creditcard_validuntilmonth")[0].value;
								r.cardcvc2 = document.getElementsByName("payonesubpay_creditcard_checkdigit")[0].value;

								var options = { return_type:"object",callback_function_name:"payoneCCResponse", async: false};
								var req = new PayoneRequest (r, options);

								req.checkAndStore();
							}
						});
						{literal}
				}
				{/literal}
				{if $autoSubmit}
				// b.form.submit();
				{/if}
				{literal}
			}

			function payoneCCResponse(resp) {
				var result = { status: resp.get ("status"), customermessage: resp.get("customermessage"), errormessage: resp.get("errormessage"), truncatedcardpan: resp.get ("truncatedcardpan"), pseudocardpan: resp.get ("pseudocardpan")};
				var error = false;

				if (result.status == "VALID") {
					var f = $('form[name="frmRegister"]')[0];

					if (! f) {
						f = $('form[class="payment"]')[0];
					}

					f.payonesubpay_creditcard_number.value = result.truncatedcardpan;
					f.payonesubpay_creditcard_pseudonumber.value = result.pseudocardpan;
					formular_may_submit = true;
				} else {
					error = true;
				}

				jQuery.ajax({
					type: 'POST',
					url: {/literal}"{url sViewport='BuiswPaymentPayone' action='logClientAPICall' forceSecure=1}", {literal}
					data: { request: data_sent, response: result},
					dataType: 'json',
					async: false,
					success: function(r) {
						var options = { return_type:"object",callback_function_name:"payoneCCResponse", async: false};
						var req = new PayoneRequest (r, options);

						req.checkAndStore();
					}
				});

				if (error) {
					alert(result.customermessage);
				} else {
					f.payonesubpay_creditcard_number.value = result.truncatedcardpan;
					f.payonesubpay_creditcard_checkdigit.value = '';

					f.submit();
				}
			}

			$(document).ready(function() {
				document.getElementById('bankgrouptype_eps').hidden = true;
				document.getElementById('bankgrouptype_idl').hidden = true;
				document.getElementById('bankcode').hidden = false;

				if(document.getElementById('onlinepay').value == "EPS") {
					document.getElementById('bankgrouptype_eps').hidden = false;
					document.getElementById('bankcode').hidden = true;
				}

				if(document.getElementById('onlinepay').value == "IDL") {
					document.getElementById('bankgrouptype_idl').hidden = false;
					document.getElementById('bankcode').hidden = true;
				}

				$('form[class="payment"]').submit(function() {
					// den haupt_radio button gibts nicht mehr...
					// var radio_button = eval ('this.elements.payment_mean' + payone_payId);
					var elems = this.elements;
					/*
					if (! radio_button.checked) {
						elems.payonesubpay_creditcard_number.value = '';
						elems.payonesubpay_creditcard_checkdigit.value = '';
						return true; // kein payone gewaehlt
					}
					*/
					var subpay_checked = null;

					for (i = 0; i < elems.payonesubpay.length; i++) {
						if (elems.payonesubpay[i].checked) subpay_checked = elems.payonesubpay[i].value;
					}

					if (subpay_checked === null) {
						return true;
					}

					if (subpay_checked == 'creditcard') {
						if (formular_may_submit) {
							elems.payonesubpay_creditcard_number.value = '';
							elems.payonesubpay_creditcard_checkdigit.value = '';

							return true;
						}

						var signstring = '';
						{/literal}
						var url = "{url sViewport='BuiswPaymentPayone' action='appendHash' forceSecure=1}";
						jQuery.ajax({
							type: 'POST',
							url: url,
							data: { cardtype: elems.payonesubpay_creditcard_card.value},
							dataType: 'json',
							async: false,
							success: function(r) {
								data_sent = r;
								r.cardpan = elems.payonesubpay_creditcard_number.value;
								r.cardtype  = elems.payonesubpay_creditcard_card.value;
								r.cardexpiredate = '' + elems.payonesubpay_creditcard_validuntilyear.value + elems.payonesubpay_creditcard_validuntilmonth.value;
								r.cardcvc2 = elems.payonesubpay_creditcard_checkdigit.value;

								var options = { return_type:"object",callback_function_name:"payoneCCResponse", async: false};
								var req = new PayoneRequest (r, options);

								req.checkAndStore();
							}
						});
						{literal}

						return false;
					}

					elems.payonesubpay_creditcard_number.value = '';
					elems.payonesubpay_creditcard_checkdigit.value = '';

					return true;
				});
			});

			function grouptypepruefung(value) {
				document.getElementById('bankgrouptype_eps').hidden = true;
				document.getElementById('bankgrouptype_idl').hidden = true;
				document.getElementById('bankcode').hidden = false;

				if(value == "EPS") {
					document.getElementById('bankgrouptype_eps').hidden = false;
					document.getElementById('bankcode').hidden = true;
				}

				if(value == "IDL") {
					document.getElementById('bankgrouptype_idl').hidden = false;
					document.getElementById('bankcode').hidden = true;
				}
			}
		</script>

		<style type="text/css">
		input.text {
			width: 150px;
		}
		</style>
	{/literal}

	{if $form_data.paypal eq true}
		<table width="100%" border="0" cellspacing="0" cellpadding="6">
			<tr>
				<th>
					<input type="radio" class="radio {$form_data.payone_radio_classes}"	name="payonesubpay" value="paypal" {if $form_data.payonesubpay == "paypal"}checked{/if} onclick="selectPayone(this)"><strong>PayPal</strong>
				</th>
			</tr>
		</table>
	{/if}

	{if $form_data.onlinepay|@count gt 0}
		<table width="100%" border="0" cellspacing="0" cellpadding="6">
			<tr>
				<th colspan="2">
					<input type="radio"	class="radio {$form_data.payone_radio_classes}" name="payonesubpay" value="onlinepay" {if $form_data.payonesubpay == "onlinepay"}checked{/if} onclick="selectPayone(this)"><strong>Online-&Uuml;berweisung</strong>
				</th>
			</tr>
			<tr>
				<td width="40%">
					Typ:
				</td>
				<td>
					<select id="onlinepay" name="payonesubpay_onlinepay_provider" size="1" onChange="grouptypepruefung(this.options[this.selectedIndex].value);" style="width:auto">
						{foreach $form_data.onlinepay as $value}
							<option value={$value.value}{if $form_data.payonesubpay_onlinepay_provider == $value.value} SELECTED{/if}>{$value.text}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr id="bankgrouptype_eps">
				<td width="40%">
					Bankgruppe:
				</td>
				<td>
					<select name="payonesubpay_onlinepay_bankgroup_eps" size="1" style="width:auto">
						{foreach $form_data.eps_values as $value}
							<option value={$value.key}{if $form_data.payonesubpay_onlinepay_bankgroup_eps == $value.key} SELECTED{/if}>{$value.text}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr id="bankgrouptype_idl">
				<td width="40%">
					Bankgruppe:
				</td>
				<td>
					<select name="payonesubpay_onlinepay_bankgroup_idl" size="1" style="width:auto">
						{foreach $form_data.idl_values as $value}
							<option value={$value.key}{if $form_data.payonesubpay_onlinepay_bankgroup_idl == $value.key} SELECTED{/if}>{$value.text}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr id="bankcode">
				<td width="40%">
					BLZ:
				</td>
				<td>
					<input type="text"	name="payonesubpay_onlinepay_bankcode" value="{$form_data.payonesubpay_onlinepay_bankcode}" class="text {if $error_flags.payonesubpay_onlinepay_bankcode}instyle_error{/if}">
				</td>
			</tr>
			<tr>
				<td width="40%">
					Kontonummer:
				</td>
				<td>
					<input type="text"	name="payonesubpay_onlinepay_accountnumber" value="{$form_data.payonesubpay_onlinepay_accountnumber}" class="text {if $error_flags.payonesubpay_onlinepay_accountnumber}instyle_error{/if}">
				</td>
			</tr>
		</table>
	{/if}

	{if $form_data.rechnung eq true}
		<table width="100%" border="0" cellspacing="0" cellpadding="6">
			<tr>
				<th colspan="2">
					<input type="radio" class="radio {$form_data.payone_radio_classes}"	name="payonesubpay" value="rechnung" {if $form_data.payonesubpay == "rechnung"}checked{/if} onclick="selectPayone(this)"><strong>Rechnung</strong>
				</th>
			</tr>
		</table>
	{/if}

	{if $form_data.lastschrift eq true}
		<table width="100%" border="0" cellspacing="0" cellpadding="6">
			<tr>
				<th colspan="2">
					<input type="radio" class="radio {$form_data.payone_radio_classes}"	name="payonesubpay" value="lastschrift" {if $form_data.payonesubpay == "lastschrift"}checked{/if} onclick="selectPayone(this)"><strong>Bankeinzug/Lastschrift</strong>
				</th>
			</tr>
			<tr>
				<td width="40%">
					BLZ:
				</td>
				<td>
					<input type="text" name="payonesubpay_directdebit_bankcode" value="{$form_data.payonesubpay_directdebit_bankcode}" class="text {if $error_flags.payonesubpay_directdebit_bankcode}instyle_error{/if}">
				</td>
			</tr>
			<tr>
				<td width="40%">
					Kontonummer:
				</td>
				<td>
					<input type="text"	name="payonesubpay_directdebit_accountnumber" value="{$form_data.payonesubpay_directdebit_accountnumber}" class="text {if $error_flags.payonesubpay_directdebit_accountnumber}instyle_error{/if}">
				</td>
			</tr>
			<tr>
				<td width="40%">
					Kontoinhaber:
				</td>
				<td>
					<input type="text"	name="payonesubpay_directdebit_depositor" value="{$form_data.payonesubpay_directdebit_depositor}" class="text {if $error_flags.payonesubpay_directdebit_depositor}instyle_error{/if}">
				</td>
			</tr>
		</table>
	{/if}

	{if $form_data.creditcards|@count gt 0}
		<table width="100%" border="0" cellspacing="0" cellpadding="6">
			<tr>
				<th colspan="2">
					<input type="radio" class="radio {$form_data.payone_radio_classes}"	name="payonesubpay" value="creditcard" {if $form_data.payonesubpay == "creditcard"}checked{/if} onclick="selectPayone(this)"><strong>Kreditkarte</strong>
					<input type="hidden" name="payonesubpay_creditcard_pseudonumber" value="{$form_data.payonesubpay_creditcard_pseudonumber}">
				</th>
			</tr>
			<tr>
				<td width="40%">
					Karte:
				</td>
				<td>
					<select name="payonesubpay_creditcard_card" size="1" style="width:auto">
						{foreach $form_data.creditcards as $value}
							<option value={$value.value}{if $form_data.payonesubpay_creditcard_card == $value.value} SELECTED{/if}>{$value.text}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td width="40%">
					Nummer:
				</td>
				<td>
					<input type="text"	name="payonesubpay_creditcard_number" value="{$form_data.payonesubpay_creditcard_number}" class="text {if $error_flags.payonesubpay_creditcard_number}instyle_error{/if}">
				</td>
			</tr>
			<tr>
				<td width="40%">
					Karteninhaber:
				</td>
				<td>
					<input type="text"	name="payonesubpay_creditcard_depositor" value="{$form_data.payonesubpay_creditcard_depositor}" class="text {if $error_flags.payonesubpay_creditcard_depositor}instyle_error{/if}">
				</td>
			</tr>
			<tr>
				<td width="40%">
					G&uuml;ltig bis:
				</td>
				<td>
					<select name="payonesubpay_creditcard_validuntilmonth" size="1" style="width:auto;">
						{foreach $form_data.months as $key => $value}
							<option value={$key}{if $form_data.payonesubpay_creditcard_validuntilmonth == $key} SELECTED{/if}>{$value}</option>
						{/foreach}
					</select>
					/
					<select name="payonesubpay_creditcard_validuntilyear" size="1" style="width:auto;">
						{foreach $form_data.years as $key => $value}
							<option value={$key}{if $form_data.payonesubpay_creditcard_validuntilyear == $key} SELECTED{/if}>{$value}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td width="40%">
					Pr&uuml;fziffer:
				</td>
				<td>
					<input type="text"	name="payonesubpay_creditcard_checkdigit" value="" class="text {if $error_flags.payonesubpay_creditcard_checkdigit}instyle_error{/if}">
				</td>
			</tr>
		</table>
	{/if}

	{if $form_data.nachnahme eq true}
		<table width="100%" border="0" cellspacing="0" cellpadding="6">
			<tr>
				<th>
					<input type="radio" class="radio {$form_data.payone_radio_classes}"	name="payonesubpay" value="nachnahme" {if $form_data.payonesubpay == "nachnahme"}checked{/if} onclick="selectPayone(this)"><strong>Nachnahme</strong>
				</th>
			</tr>
		</table>
	{/if}

	{if $form_data.vorkasse eq true}
		<table width="100%" border="0" cellspacing="0" cellpadding="6">
			<tr>
				<th>
					<input type="radio" class="radio {$form_data.payone_radio_classes}"	name="payonesubpay" value="vorkasse" {if $form_data.payonesubpay == "vorkasse"}checked{/if} onclick="selectPayone(this)"><strong>Vorkasse</strong>
				</th>
			</tr>
		</table>
	{/if}
</div>