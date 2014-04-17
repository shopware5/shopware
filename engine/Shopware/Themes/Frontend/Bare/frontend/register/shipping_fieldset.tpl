<div class="panel register--shipping">
	<h2 class="panel--title underline">{s name='RegisterShippingHeadline'}{/s}</h2>
	<div class="panel--body">
		{* Salutation *}
		{block name='frontend_register_shipping_fieldset_input_salutation'}
			<div class="field--select">
				<span class="arrow"></span>
				<select name="register[shipping][salutation]" id="salutation2" class="normal {if $error_flags.salutation}instyle_error{/if}">
					<option>{s name='RegisterShippingLabelSalutation'}{/s}</option>
					<option value="mr" {if $form_data.salutation eq "mr"}selected="selected"{/if}>{s name='RegisterShippingLabelMr'}{/s}</option>
					<option value="ms" {if $form_data.salutation eq "ms"}selected="selected"{/if}>{s name='RegisterShippingLabelMrs'}{/s}</option>
				</select>
			</div>
		{/block}

		{* Company *}
		{block name="frontend_register_shipping_fieldset_input_company"}
			<div class="register--companyname">
				<input name="register[shipping][company]" type="text" placeholder="{s name='RegisterShippingLabelCompany'}{/s}" id="company2" value="{$form_data.company|escape}" class="register--field text {if $error_flags.company}instyle_error{/if}" />
			</div>
		{/block}

		{* Department *}
		{block name='frontend_register_shipping_fieldset_input_department'}
			<div class="register--department">
				<input name="register[shipping][department]" type="text" placeholder="{s name='RegisterShippingLabelDepartment'}{/s}" id="department2" value="{$form_data.department|escape}" class="register--field" />
			</div>
		{/block}

		{* Firstname *}
		{block name='frontend_register_shipping_fieldset_input_firstname'}
			<div class="register--firstname">
				<input name="register[shipping][firstname]" type="text" placeholder="{s name='RegisterShippingLabelFirstname'}{/s}" id="firstname2" value="{$form_data.firstname|escape}" class="register--field required {if $error_flags.firstname}instyle_error{/if}" />
			</div>
		{/block}

		{* Lastname *}
		{block name='frontend_register_shipping_fieldset_input_lastname'}
			<div class="register--lastname">
				<input name="register[shipping][lastname]" type="text" placeholder="{s name='RegisterShippingLabelLastname'}{/s}" id="lastname2" value="{$form_data.lastname|escape}" class="register--field required {if $error_flags.lastname}instyle_error{/if}" />
			</div>
		{/block}

		{* Street *}
		{block name='frontend_register_shipping_fieldset_input_street'}
			<div class="register--street">
				<input name="register[shipping][street]" type="text" placeholder="{s name='RegisterShippingLabelStreet'}{/s}" id="street2" value="{$form_data.street|escape}" class="register--field street required {if $error_flags.street}instyle_error{/if}" />
				<input name="register[shipping][streetnumber]" type="text" placeholder="{s name='RegisterShippingLabelStreetNumber'}{/s}" id="streetnumber2" value="{$form_data.streetnumber|escape}" class="register--field streetnumber required {if $error_flags.streetnumber}instyle_error{/if}" />
			</div>
		{/block}

	{* Additional Address Line 1 *}
	{block name='frontend_register_shipping_fieldset_input_addition_address_line1'}
		{if {config name=showAdditionAddressLine1}}
			<div>
				<label for="additionalAddressLine21" {if !{config name=requireAdditionAddressLine1}}class="normal"{/if}>{se name='RegisterLabelAdditionalAddressLine1'}{/se}{if {config name=requireAdditionAddressLine1}}*{/if}:</label>
				<input name="register[shipping][additional_address_line1]" type="text" id="additionalAddressLine21" value="{$form_data.additional_address_line1|escape}" class="text {if {config name=requireAdditionAddressLine1}}required{/if} {if $error_flags.additional_address_line1 && {config name=requireAdditionAddressLine1}}instyle_error{/if}" />
			</div>
		{/if}
	{/block}

	{* Additional Address Line 2 *}
	{block name='frontend_register_shipping_fieldset_input_addition_address_line2'}
		{if {config name=showAdditionAddressLine2}}
			<div>
				<label for="additionalAddressLine22" {if !{config name=requireAdditionAddressLine2}}class="normal"{/if}>{se name='RegisterLabelAdditionalAddressLine2'}{/se}{if {config name=requireAdditionAddressLine2}}*{/if}:</label>
				<input name="register[shipping][additional_address_line2]" type="text" id="additionalAddressLine22" value="{$form_data.additional_address_line2|escape}" class="text {if {config name=requireAdditionAddressLine2}}required{/if} {if $error_flags.additional_address_line2 && {config name=requireAdditionAddressLine2}}instyle_error{/if}" />
			</div>
		{/if}
	{/block}


		{* Zip + City *}
		{block name='frontend_register_shipping_fieldset_input_zip_and_city'}
			<div class="register--zip-city">
				<input name="register[shipping][zipcode]" type="text" placeholder="{s name='RegisterShippingLabelZipcode'}{/s}" id="zipcode2" value="{$form_data.zipcode|escape}"  class="register--field zipcode required {if $error_flags.zipcode}instyle_error{/if}" />
				<input name="register[shipping][city]" type="text" placeholder="{s name='RegisterShippingLabelCity'}{/s}" id="city2" value="{$form_data.city|escape}" size="25" class="register--field city required {if $error_flags.city}instyle_error{/if}" />
			</div>
		{/block}

		{* Country *}
		{if {config name=CountryShipping}}
			{block name='frontend_register_shipping_fieldset_input_country'}
				<div class="field--select">
					<span class="arrow"></span>
					<select name="register[shipping][country]" id="country2" class="required {if $error_flags.country}instyle_error{/if}">
						<option value="" selected="selected">{s name='RegisterShippingLabelCountry'}{/s}</option>
						{foreach from=$country_list item=country}
							<option value="{$country.id}"{if $country.id eq $form_data.country} selected="selected"{/if}>
							{$country.countryname}
							</option>
						{/foreach}
					</select>
				</div>
			{/block}
		{/if}

		{* Country state *}
		{block name='frontend_register_shipping_fieldset_input_country_states'}
			<div class="country-area-state-selection">
				{foreach $country_list as $country}
					{if $country.states}
						<div class="field--select selection{if $country.id != $form_data.country} is--disabled{/if}">
							<span class="arrow small"></span>
							<select {if $country.id != $form_data.country}disabled="disabled"{/if} name="register[shipping][country_shipping_state_{$country.id}]" id="country_{$country.id}_states" class="{if $country.force_state_in_registration}required{/if} {if $error_flags.stateID}instyle_error{/if}">
							<option value="" selected="selected">{s name='RegisterShippingLabelState'}Bundesstaat:{/s}</option>
								{assign var="stateID" value="country_shipping_state_`$country.id`"}
								{foreach from=$country.states item=state}
									<option value="{$state.id}" {if $state.id eq $form_data[$stateID]}selected="selected"{/if}>{$state.name}</option>
								{/foreach}
							</select>
						</div>
					{/if}
				{/foreach}
			</div>
		{/block}
	</div>
</div>
