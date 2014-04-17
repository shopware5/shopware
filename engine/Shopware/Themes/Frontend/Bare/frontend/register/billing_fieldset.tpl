<div class="panel register--company">
	<h2 class="panel--title underline">{s name='RegisterHeaderCompany'}{/s}</h2>
	<div class="panel--body">

		{* Company *}
		{block name='frontend_register_billing_fieldset_input_company'}
			<div class="register--companyname">
				<input name="register[billing][company]" type="text" placeholder="{s name='RegisterLabelCompany'}{/s}" id="register_billing_company" value="{$form_data.company|escape}" class="register--field required {if $error_flags.company}instyle_error{/if}" />
			</div>
		{/block}

		{* Department *}
		{block name='frontend_register_billing_fieldset_input_department'}
			<div class="register--department">
				<input name="register[billing][department]" type="text" placeholder="{s name='RegisterLabelDepartment'}{/s}" id="register_billing_department" value="{$form_data.department|escape}" class="register--field" />
			</div>
		{/block}

		{* UST Id *}
		{block name='frontend_register_billing_fieldset_input_ustid'}
			<div class="register--ustid">
				<input name="register[billing][ustid]" type="text" placeholder="{s name='RegisterLabelTaxId'}{/s}" id="register_billing_ustid" value="{$form_data.ustid|escape}" class="register--field {if $error_flags.ustid}instyle_error{/if}" />
			</div>
		{/block}

	</div>
</div>

<div class="panel register--address">
	<h2 class="panel--title underline">{s name='RegisterBillingHeadline'}{/s}</h2>
	<div class="panel--body">

		{* Street *}
		{block name='frontend_register_billing_fieldset_input_street'}
			<div class="register--street">
				<input name="register[billing][street]" type="text" placeholder="{s name='RegisterBillingLabelStreet'}{/s}" id="street" value="{$form_data.street|escape}" class="register--field street required text{if $error_flags.street} instyle_error{/if}" />
				<input name="register[billing][streetnumber]" type="text" placeholder="{s name='RegisterBillingLabelStreetNumber'}{/s}" id="streetnumber" value="{$form_data.streetnumber|escape}"  class="register--field streetnumber required text{if $error_flags.streetnumber} instyle_error{/if}" />
			</div>
		{/block}

	{* Additional Address Line 1 *}
	{block name='frontend_register_billing_fieldset_input_addition_address_line1'}
		{if {config name=showAdditionAddressLine1}}
			<div>
				<label for="additionalAddressLine1" {if !{config name=requireAdditionAddressLine1}}class="normal"{/if}>{se name='RegisterLabelAdditionalAddressLine1'}{/se}{if {config name=requireAdditionAddressLine1}}*{/if}:</label>
				<input name="register[billing][additional_address_line1]" type="text" id="additionalAddressLine1" value="{$form_data.additional_address_line1|escape}" class="text {if {config name=requireAdditionAddressLine1}}required{/if} {if $error_flags.additional_address_line1 && {config name=requireAdditionAddressLine1}}instyle_error{/if}" />
			</div>
		{/if}
	{/block}

	{* Additional Address Line 2 *}
	{block name='frontend_register_billing_fieldset_input_addition_address_line2'}
		{if {config name=showAdditionAddressLine2}}
			<div>
				<label for="additionalAddressLine2" {if !{config name=requireAdditionAddressLine2}}class="normal"{/if}>{se name='RegisterLabelAdditionalAddressLine2'}{/se}{if {config name=requireAdditionAddressLine2}}*{/if}:</label>
				<input name="register[billing][additional_address_line2]" type="text" id="additionalAddressLine2" value="{$form_data.additional_address_line2|escape}" class="text {if {config name=requireAdditionAddressLine2}}required{/if} {if $error_flags.additional_address_line2 && {config name=requireAdditionAddressLine2}}instyle_error{/if}" />
			</div>
		{/if}
	{/block}

	{* Zip + City *}
	{block name='frontend_register_billing_fieldset_input_zip_and_city'}
		<div class="register--zip-city">
			<input name="register[billing][zipcode]" type="text" id="zipcode" value="{$form_data.zipcode|escape}" placeholder="{s name='RegisterBillingLabelZipcode'}{/s}"  class="register--field zipcode required text{if $error_flags.zipcode} instyle_error{/if}" />
			<input name="register[billing][city]" type="text" id="city" value="{$form_data.city|escape}" placeholder="{s name='RegisterBillingLabelCity'}{/s}" size="25" class="register--field city required text{if $error_flags.city} instyle_error{/if} />
		</div>
	{/block}

		{* Country *}
		{block name='frontend_register_billing_fieldset_input_country'}
			<div class="field--select country">
				<span class="arrow"></span>
				<select name="register[billing][country]" id="country" class="text required {if $error_flags.country}instyle_error{/if}">
				<option value="" selected="selected">{s name='RegisterBillingLabelCountry'}{/s}</option>
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
					<div class="field--select selection{if $country.id != $form_data.country} is--disabled{/if}">
						<span class="arrow"></span>
						<select {if $country.id != $form_data.country}disabled="disabled"{/if} name="register[billing][country_state_{$country.id}]" id="country_{$country.id}_states" class="{if $country.force_state_in_registration}required{/if} {if $error_flags.stateID}instyle_error{/if}">
						<option value="" selected="selected">{s name='RegisterBillingLabelState'}Bundesstaat:{/s}</option>
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
				</div>
			{/if}
		{/block}
	</div>
</div>
