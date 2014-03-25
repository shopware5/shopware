<div class="company_informations">
	<h2 class="headingbox_dark largesize">{s name='RegisterHeaderCompany'}{/s}</h2>

	{* Company *}
	{block name='frontend_register_billing_fieldset_input_company'}
		<div>
			<label for="register_billing_company">{se name='RegisterLabelCompany'}{/se}:</label>
			<input name="register[billing][company]" type="text"  id="register_billing_company" value="{$form_data.company|escape}" class="text company required {if $error_flags.company}instyle_error{/if}" />
		</div>
	{/block}

	{* Department *}
	{block name='frontend_register_billing_fieldset_input_department'}
		<div>
			<label for="register_billing_department" class="normal">{se name='RegisterLabelDepartment'}{/se}:</label>
			<input name="register[billing][department]" type="text"  id="register_billing_department" value="{$form_data.department|escape}" class="text" />
		</div>
	{/block}

	{* UST Id *}
	{block name='frontend_register_billing_fieldset_input_ustid'}
		<div>
			<label for="register_billing_ustid" class="normal">{se name='RegisterLabelTaxId'}{/se}:</label>
			<input name="register[billing][ustid]" type="text"  id="register_billing_ustid" value="{$form_data.ustid|escape}" class="text {if $error_flags.ustid}instyle_error{/if}" />
		</div>
	{/block}
</div>

<div class="shipping_address">
	<h2 class="headingbox_dark largesize">{s name='RegisterBillingHeadline'}{/s}</h2>

	{* Street *}
	{block name='frontend_register_billing_fieldset_input_street'}
		<div>
			<label for="street">{s name='RegisterBillingLabelStreet'}{/s}</label>
			<input name="register[billing][street]" type="text"  id="street" value="{$form_data.street|escape}" class="street required text{if $error_flags.street} instyle_error{/if}" />
			<input name="register[billing][streetnumber]" type="text"  id="streetnumber" value="{$form_data.streetnumber|escape}"  class="number streetnumber required text{if $error_flags.streetnumber} instyle_error{/if}" />
		</div>
	{/block}

	{* Zip + City *}
	{block name='frontend_register_billing_fieldset_input_zip_and_city'}
		<div>
			<label for="zipcode">{se name='RegisterBillingLabelCity'}{/se}</label>
			<input name="register[billing][zipcode]" type="text" id="zipcode" value="{$form_data.zipcode|escape}" class="zipcode required text{if $error_flags.zipcode} instyle_error{/if}" />
			<input name="register[billing][city]" type="text" id="city" value="{$form_data.city|escape}" size="25" class="city required text{if $error_flags.city} instyle_error{/if}" />
		</div>
	{/block}

	{* Country *}
	{block name='frontend_register_billing_fieldset_input_country'}
		<div>
		<label for="country">{se name='RegisterBillingLabelCountry'}{/se} </label>
			<select name="register[billing][country]" id="country" class="text required {if $error_flags.country}instyle_error{/if}">
			<option value="" selected="selected">{s name='RegisterBillingLabelSelect'}{/s}</option>
			{foreach from=$country_list item=country}
				<option value="{$country.id}" {if $country.id eq $form_data.country}selected="selected"{/if} {if $country.states}stateSelector="country_{$country.id}_states"{/if}>
				{$country.countryname}
				</option>
			{/foreach}
			</select>
		</div>
	{/block}

    {* Country state *}
    {block name='frontend_register_billing_fieldset_input_country_states'}
    <div class="country-area-state-selection">
        {foreach $country_list as $country}
            {if $country.states}
                <div class="selection{if $country.id != $form_data.country} hidden{/if}">
                <label for="country_{$country.id}_states">{se name='RegisterBillingLabelState'}Bundesstaat:{/se} </label>
                    <select {if $country.id != $form_data.country}disabled="disabled"{/if} name="register[billing][country_state_{$country.id}]" id="country_{$country.id}_states" class="text {if $country.force_state_in_registration}required{/if} {if $error_flags.stateID}instyle_error{/if}">
                    <option value="" selected="selected">{s name='RegisterBillingLabelSelect'}{/s}</option>
                        {assign var="stateID" value="country_state_`$country.id`"}
                        {foreach from=$country.states item=state}
                            <option value="{$state.id}" {if $state.id eq $form_data[$stateID]}selected="selected"{/if}>{$state.name}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
        {/foreach}
    </div>
    {/block}

	{* Alternative *}
	{block name='frontend_register_billing_fieldset_different_shipping'}
		{if !$update}
			<div class="alt_shipping">
				<input name="register[billing][shippingAddress]" type="checkbox" id="register_billing_shippingAddress" value="1" class="chkbox" {if $form_data.shippingAddress}checked="checked"{/if} />
				<label for="register_billing_shippingAddress">{s name='RegisterBillingLabelShipping'}{/s}</label>
				<div class="clear">&nbsp;</div>
			</div>
		{/if}
	{/block}
</div>
