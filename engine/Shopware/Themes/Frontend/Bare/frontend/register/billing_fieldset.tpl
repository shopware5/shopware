<div class="panel register--company">
	<h2 class="panel--title is--underline">{s name='RegisterHeaderCompany'}{/s}</h2>
	<div class="panel--body">

		{* Company *}
		{block name='frontend_register_billing_fieldset_input_company'}
			<div class="register--companyname">
				<input name="register[billing][company]" type="text" aria-required="true" placeholder="{s name='RegisterLabelCompany'}{/s}" id="register_billing_company" value="{$form_data.company|escape}" class="register--field is--required {if $error_flags.company}has--error{/if}" />
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
				<input name="register[billing][ustid]" type="text" placeholder="{s name='RegisterLabelTaxId'}{/s}" id="register_billing_ustid" value="{$form_data.ustid|escape}" class="register--field{if $error_flags.ustid} has--error{/if}" />
			</div>
		{/block}

	</div>
</div>

<div class="panel register--address">
	<h2 class="panel--title is--underline">{s name='RegisterBillingHeadline'}{/s}</h2>
	<div class="panel--body">

		{* Street *}
		{block name='frontend_register_billing_fieldset_input_street'}
			<div class="register--street">
				<input name="register[billing][street]" type="text" aria-required="true" placeholder="{s name='RegisterBillingLabelStreet'}{/s}" id="street" value="{$form_data.street|escape}" class="register--field register--field-street is--required{if $error_flags.street} has--error{/if}" />
				<input name="register[billing][streetnumber]" type="text" aria-required="true" placeholder="{s name='RegisterBillingLabelStreetNumber'}{/s}" id="streetnumber" value="{$form_data.streetnumber|escape}"  class="register--field register--field-streetnumber is--required{if $error_flags.streetnumber} has--error{/if}" />
			</div>
		{/block}

		{* Additional Address Line 1 *}
		{block name='frontend_register_billing_fieldset_input_addition_address_line1'}
			{if {config name=showAdditionAddressLine1}}
				<div class="register--additional-line1">
					<input name="register[billing][additional_address_line1]" type="text" {if {config name=requireAdditionAddressLine1}}aria-required="true"{/if} placeholder="{s name='RegisterLabelAdditionalAddressLine1'}{/s}{if {config name=requireAdditionAddressLine1}}*{/if}" id="additionalAddressLine1" value="{$form_data.additional_address_line1|escape}" class="register--field {if {config name=requireAdditionAddressLine1}}is--required{/if} {if $error_flags.additional_address_line1 && {config name=requireAdditionAddressLine1}}has--error{/if}" />
				</div>
			{/if}
		{/block}

		{* Additional Address Line 2 *}
		{block name='frontend_register_billing_fieldset_input_addition_address_line2'}
			{if {config name=showAdditionAddressLine2}}
				<div class="register--additional-field2">
					<input name="register[billing][additional_address_line2]" type="text" {if {config name=requireAdditionAddressLine2}}aria-required="true"{/if} placeholder="{s name='RegisterLabelAdditionalAddressLine2'}{/s}{if {config name=requireAdditionAddressLine2}}*{/if}" id="additionalAddressLine2" value="{$form_data.additional_address_line2|escape}" class="register--field {if {config name=requireAdditionAddressLine2}}is--required{/if} {if $error_flags.additional_address_line2 && {config name=requireAdditionAddressLine2}}has--error{/if}" />
				</div>
			{/if}
		{/block}

		{* Zip + City *}
		{block name='frontend_register_billing_fieldset_input_zip_and_city'}
			<div class="register--zip-city">
				<input name="register[billing][zipcode]" type="text" aria-required="true" placeholder="{s name='RegisterBillingLabelZipcode'}{/s}" id="zipcode" value="{$form_data.zipcode|escape}" class="register--field register--field-zipcode is--required{if $error_flags.zipcode} has--error{/if}" />
				<input name="register[billing][city]" type="text" aria-required="true" placeholder="{s name='RegisterBillingLabelCity'}{/s}" id="city" value="{$form_data.city|escape}" size="25" class="register--field register--field-city is--required{if $error_flags.city} has--error{/if}" />
			</div>
		{/block}

		{* Country *}
		{block name='frontend_register_billing_fieldset_input_country'}
			<div class="register--country field--select">
				<span class="arrow"></span>
				<select name="register[billing][country]" id="country" class="is--required{if $error_flags.country} has--error{/if}">
				<option value="" selected="selected">{s name='RegisterBillingLabelCountry'}{/s}</option>
				{foreach $country_list as $country}
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
					<div class="register--state-selection field--select{if $country.id != $form_data.country} is--disabled{/if}">
						<span class="arrow"></span>
						<select {if $country.id != $form_data.country}disabled="disabled"{/if} name="register[billing][country_state_{$country.id}]" id="country_{$country.id}_states" class="{if $country.force_state_in_registration}is--required{/if} {if $error_flags.stateID}has--error{/if}">
						<option value="" selected="selected">{s name='RegisterBillingLabelState'}Bundesstaat:{/s}</option>
							{assign var="stateID" value="country_state_`$country.id`"}
							{foreach $country.states as $state}
								<option value="{$state.id}" {if $state.id eq $form_data[$stateID]}selected="selected"{/if}>
									{$state.name}
								</option>
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
				<div class="register--alt-shipping">
					<input name="register[billing][shippingAddress]" type="checkbox" id="register_billing_shippingAddress" value="1" class="chkbox" {if $form_data.shippingAddress}checked="checked"{/if} />
					<label for="register_billing_shippingAddress">{s name='RegisterBillingLabelShipping'}{/s}</label>
				</div>
			{/if}
		{/block}
	</div>
</div>
