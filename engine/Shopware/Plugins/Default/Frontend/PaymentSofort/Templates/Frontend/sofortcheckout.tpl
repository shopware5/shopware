{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-07-09 11:10:01 +0200 (Mon, 09 Jul 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0 $Id: sofortcheckout.tpl 4656 2012-07-09 09:10:01Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}
{extends file="frontend/index/header.tpl"}
<!-- Add a border to modal box showing "Datenschutzhinweise" -->
{block name="frontend_index_header_css_screen" append}
	<style type="text/css">
	{literal}
		div .dhwframe #content {padding:4px;}
	{/literal} 
	</style>
{/block}


<!-- Attach some JavaScript functionality to head -->
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
$(document).ready(function(){
	$('#vorkassebysofort_dhw').hide();
	$('#sofortrechnung_dhw').hide();
	$('#lastschriftbysofort_dhw').hide();

	var mandatoryColour = '#FBC2C4';
	
	var paymentRadioButtons = new Array();	// construct an array containing the IDs of all radio buttons
	{foreach from=$sofortPaymentMeans item=payment_mean name=register_payment_mean_js}
		paymentRadioButtons.push("payment_mean{$payment_mean.id}");
	{/foreach}

	var modalConfig = {
		'position': 'absolute',
		'animationSpeed': 200,
		'width': '480px',
		'textContainer': '<div>',
		'textClass': 'dhwframe'
	};

	for(i=0; i<paymentRadioButtons.length; i++) {
		// fetch the checkbox, if there is any
		//checkbox = $(':input[name='+actualCheckboxName+']');
		
		var checkbox = $('#'+paymentRadioButtons[i]).parent().next().find(':input[type=checkbox]');
		if(typeof checkbox != "undefined") {
			
			$(checkbox).click(function() {
				submitForm(this);
			});
		}

		// iterate through any payment means coming from payment network
		$('#'+paymentRadioButtons[i]).click(function(e) {
			var parentDiv = $(this).parent().parent();
			var actualCheckbox = $(parentDiv).find(':input[type=checkbox]');
			var actualCheckboxName = $(actualCheckbox).attr('name');

			if(actualCheckbox != '') {
				dhwContent = $('#'+actualCheckboxName).html();
			}
			// is checked and not undefined
			if(typeof actualCheckboxName != "undefined" && $(actualCheckbox).attr('checked') != true) {
				// set the background colour of all missing mandatory fields
				checkboxParent = $(':input[name='+actualCheckboxName+']').parent();
				$(checkboxParent).css('background', mandatoryColour);

				// find any additional input fields
				inputs = $(checkboxParent).parent().find(':input[type=text]');
				// iterate through any found empty fields and set the background colour accordingly
				jQuery.each(inputs, function(index, value) {
					inputval = $(this).attr('value');
					if(inputval == '') $(this).css('background', mandatoryColour);
				});
			} 
			else {
				// submit form if fields filled out correctly
				var parentForm = $(parentDiv).closest("form");
				parentForm.submit();
			}
		});
	}
	// remove the save-button 
	var submitButton = $('.actions .button-right');
	$('.payment_method :input[type=submit]').remove();
});

// fetch the checkbox for this DHW and set it to checked, then submit() the form
function setDHW(name) {
	var dhwCheckbox = $(':input[name='+name+']');
	$(dhwCheckbox).attr('checked', true);
	var parentForm = $(dhwCheckbox).closest("form");
	parentForm.submit();
}

function showDWH(type) {
	if(type == 'sv') {
		$('#vorkassebysofort_dhw').slideToggle();
	} else if(type == 'sr') {
		$('#sofortrechnung_dhw').slideToggle();
	} else if(type == 'ls') {
		$('#lastschriftbysofort_dhw').slideToggle();
	}
}

function submitForm(element) {
	checkboxParent = $(element).parent().parent().parent().parent();
	var radio = $(checkboxParent).find(':input[type=radio]');
	if(typeof radio != "undefined") {
		checked = $(radio).attr('checked');
		if(checked) {
			parentForm = $(radio).closest("form");
			parentForm.submit();
		}
	}
}
</script>
{/block}


<!-- Extend the payment fieldsets -->
{extends file="frontend/checkout/confirm_payment.tpl"}
{block name='frontend_checkout_payment_fieldset_input_radio'}
	<div class="grid_5 first">
		<input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq  $chosenPaymentMethod} checked="checked"{/if} /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
	</div>
{/block}
{block name='frontend_checkout_payment_fieldset_description'}
	{if $sofortPaymentMeans[$payment_mean.id].name eq 'sofortlastschrift_multipay'}
		{include file="sofortlastschrift.tpl"}
	{elseif $sofortPaymentMeans[$payment_mean.id].name eq 'lastschriftbysofort_multipay'}
		{include file="lastschriftbysofort.tpl"}
	{elseif $sofortPaymentMeans[$payment_mean.id].name eq 'vorkassebysofort_multipay'}
		{include file="vorkassebysofort.tpl"}
	{elseif $sofortPaymentMeans[$payment_mean.id].name eq 'sofortrechnung_multipay'}
		{include file="sofortrechnung.tpl"}
	{elseif $sofortPaymentMeans[$payment_mean.id].name eq 'sofortueberweisung_multipay'}
		{include file="sofortueberweisung.tpl"}
	{else}
		<div class="grid_10 last">
			{$payment_mean.additionaldescription}
		</div>
	{/if}
{/block}

{block name='frontend_checkout_payment_fieldset_template'}
<div class="payment_logo_{$payment_mean.name}"></div>
{if "frontend/plugins/payment/`$payment_mean.template`"|template_exists}
	<div class="space">&nbsp;</div>
	<div class="grid_8 bankdata">
		{include file="frontend/plugins/payment/`$payment_mean.template`"}
	</div>
{/if}
{/block}