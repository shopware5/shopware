<div class="panel register--personal">
	<h2 class="panel--title is--underline">{if isset($fieldset_title) && !empty($fieldset_title)}{$fieldset_title}{else}{s name='RegisterPersonalMarketingHeadline'}{/s}{/if}</h2>
	<div class="panel--body is--wide">
		{* Customer type *}
		{block name='frontend_register_personal_fieldset_customer_type'}
			{if $form_data.sValidation}
				<input type="hidden" name="register[personal][sValidation]" value="{$form_data.sValidation|escape}" />
			{else}
				<div class="register--customertype">
				{if {config name=showCompanySelectField}}
					<select id="register_personal_customer_type" name="register[personal][customer_type]" required="required" aria-required="true" class="is--required{if $error_flags.customer_type} has--error{/if}">
						<option value="" disabled="disabled"{if $form_data.customer_type eq ""} selected="selected"{/if}>{s name='RegisterPersonalLabelType'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}</option>
						<option value="private"{if $form_data.customer_type eq "private" or (!$form_data.company and $sUserLoggedIn)} selected="selected"{/if}>{s name='RegisterPersonalLabelPrivate'}{/s}</option>
						<option value="business"{if $form_data.customer_type eq "business" or $form_data.company or $form_data.sValidation} selected="selected"{/if}>{s name='RegisterPersonalLabelBusiness'}{/s}</option>
					</select>
				{else}
					{* Always register as a private customer*}
					<div class="is--hidden">
						<select id="register_personal_customer_type" name="register[personal][customer_type]">
							<option value="private" selected="selected">{s name='RegisterPersonalLabelPrivate'}{/s}</option>
						</select>
					</div>
				{/if}
				</div>
			{/if}
		{/block}

		{* Salutation *}
		{block name='frontend_register_personal_fieldset_salutation'}
			<div class="register--salutation field--select">
				<select name="register[personal][salutation]" id="salutation" required="required" aria-required="true" class="is--required{if $error_flags.salutation} has--error{/if}">
                    <option value="" disabled="disabled"{if $form_data.salutation eq ""} selected="selected"{/if}>{s name='RegisterPlaceholderSalutation'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}</option>
					<option value="mr"{if $form_data.salutation eq "mr"} selected="selected"{/if}>{s name='RegisterLabelMr'}{/s}</option>
					<option value="ms"{if $form_data.salutation eq "ms"} selected="selected"{/if}>{s name='RegisterLabelMs'}{/s}</option>
				</select>
			</div>
		{/block}

		{* Firstname *}
		{block name='frontend_register_personal_fieldset_input_firstname'}
			<div class="register--firstname">
				<input autocomplete="section-personal given-name" name="register[personal][firstname]" type="text" required="required" aria-required="true" placeholder="{s name='RegisterPlaceholderFirstname'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}" id="firstname" value="{$form_data.firstname|escape}" class="register--field is--required{if $error_flags.firstname} has--error{/if}" />
			</div>
		{/block}

		{* Lastname *}
		{block name='frontend_register_personal_fieldset_input_lastname'}
			<div class="register--lastname">
				<input autocomplete="section-personal family-name" name="register[personal][lastname]" type="text" required="required" aria-required="true" placeholder="{s name='RegisterPlaceholderLastname'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}" id="lastname" value="{$form_data.lastname|escape}" class="register--field is--required{if $error_flags.lastname} has--error{/if}" />
			</div>
		{/block}

		{* Skip login *}
		{if !$update}
			{block name='frontend_register_personal_fieldset_skip_login'}
				{if ($showNoAccount || $form_data.skipLogin) && !$sEsd && !$form_data.sValidation && !{config name=NoAccountDisable}}
					<div class="register--check">
						<input type="checkbox" value="1" id="register_personal_skipLogin" name="register[personal][skipLogin]" class="register--checkbox chkbox" {if $form_data.skipLogin || $form_data.accountmode || $skipLogin}checked="checked" {/if}/>
						<label for="register_personal_skipLogin" class="chklabel is--bold">{s name='RegisterLabelNoAccount'}{/s}</label>
					</div>
				{/if}
			{/block}

			{* E-Mail *}
			{block name='frontend_register_personal_fieldset_input_mail'}
				<div class="register--email">
					<input autocomplete="section-personal email" name="register[personal][email]" type="email" required="required" aria-required="true" placeholder="{s name='RegisterPlaceholderMail'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}" id="register_personal_email" value="{$form_data.email|escape}" class="register--field email is--required{if $error_flags.email} has--error{/if}" />
				</div>

				{if {config name=doubleEmailValidation}}
					<div class="register--emailconfirm">
						<input autocomplete="section-personal email" name="register[personal][emailConfirmation]" type="email" required="required" aria-required="true" placeholder="{s name='RegisterPlaceholderMailConfirmation'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}" id="register_personal_emailConfirmation" value="{$form_data.emailConfirmation|escape}" class="register--field emailConfirmation is--required{if $error_flags.emailConfirmation} has--error{/if}" />
					</div>
				{/if}
			{/block}
		{/if}

		{if !$update}
			<div class="register--account-information">
				{* Password *}
				{block name='frontend_register_personal_fieldset_input_password'}
					<div class="register--password">
						<input name="register[personal][password]" type="password" required="required" aria-required="true" placeholder="{s name='RegisterPlaceholderPassword'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}" id="register_personal_password" class="register--field password is--required{if $error_flags.password} has--error{/if}" />
					</div>
				{/block}

				{* Password confirmation *}
				{block name='frontend_register_personal_fieldset_input_password_confirm'}
					{if {config name=doublePasswordValidation}}
						<div class="register--passwordconfirm">
							<input name="register[personal][passwordConfirmation]" type="password" aria-required="true" placeholder="{s name='RegisterPlaceholderPasswordRepeat'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}" id="register_personal_passwordConfirmation" class="register--field passwordConfirmation is--required{if $error_flags.passwordConfirmation} has--error{/if}" />
						</div>
					{/if}
				{/block}

				{* Password description *}
				{block name='frontend_register_personal_fieldset_password_description'}
					<div class="register--password-description">
						{s name='RegisterInfoPassword'}{/s} {config name=MinPassword} {s name='RegisterInfoPasswordCharacters'}{/s}<br />{s name='RegisterInfoPassword2'}{/s}
					</div>
				{/block}
			</div>
		{/if}

		{* Phone *}
		{block name='frontend_register_personal_fieldset_input_phone'}
			{if {config name=showPhoneNumberField}}
				<div class="register--phone">
					<input autocomplete="section-personal tel" name="register[personal][phone]" type="tel"{if {config name=requirePhoneField}} required="required" aria-required="true"{/if} placeholder="{s name='RegisterPlaceholderPhone'}{/s}{if {config name=requirePhoneField}}{s name="RequiredField" namespace="frontend/register/index"}{/s}{/if}" id="phone" value="{$form_data.phone|escape}" class="register--field{if {config name=requirePhoneField}} is--required{/if}{if $error_flags.phone && {config name=requirePhoneField}} has--error{/if}" />
				</div>
			{/if}
		{/block}

		{* Birthday *}
		{if {config name=showBirthdayField} && !$update}
            {block name='frontend_register_personal_fieldset_birthday'}
                <div class="register--birthdate">
                    <label for="register_personal_birthdate" class="birthday--label">{s name='RegisterPlaceholderBirthday'}{/s}{if {config name=requireBirthdayField}}{s name="RequiredField" namespace="frontend/register/index"}{/s}{/if}</label>

                    <div class="register--birthday field--select">
                        <select id="register_personal_birthdate" name="register[personal][birthday]"{if {config name=requireBirthdayField}} required="required" aria-required="true"{/if} class="{if {config name=requireBirthdayField}}is--required{/if}{if $error_flags.birthday && {config name=requireBirthdayField}} has--error{/if}">
                            <option value=""{if {config name=requireBirthdayField}} disabled="disabled"{/if}{if $form_data.birthday eq ""} selected="selected"{/if}>{s name='RegisterBirthdaySelectDay'}{/s}</option>
                            {section name="birthdate" start=1 loop=32 step=1}
                                <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                            {/section}
                        </select>
                    </div>

                    <div class="register--birthmonth field--select">
                        <select name="register[personal][birthmonth]"{if {config name=requireBirthdayField}} required="required" aria-required="true"{/if} class="{if {config name=requireBirthdayField}}is--required{/if}{if $error_flags.birthmonth && {config name=requireBirthdayField}} has--error{/if}">
                            <option value=""{if {config name=requireBirthdayField}} disabled="disabled"{/if}{if $form_data.birthmonth eq ""} selected="selected"{/if}>{s name='RegisterBirthdaySelectMonth'}{/s}</option>
                            {section name="birthmonth" start=1 loop=13 step=1}
                                <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                            {/section}
                        </select>
                    </div>

                    <div class="register--birthyear field--select">
                        <select name="register[personal][birthyear]"{if {config name=requireBirthdayField}} required="required" aria-required="true"{/if} class="{if {config name=requireBirthdayField}}is--required{/if}{if $error_flags.birthyear && {config name=requireBirthdayField}} has--error{/if}">
                            <option value=""{if {config name=requireBirthdayField}} disabled="disabled"{/if}{if $form_data.birthyear eq ""} selected="selected"{/if}>{s name='RegisterBirthdaySelectYear'}{/s}</option>
                            {section name="birthyear" loop={$smarty.now|date_format:"%Y"} max={$smarty.now|date_format:"%Y"}-1900 step=-1}
                                <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                            {/section}
                        </select>
                    </div>
                </div>
            {/block}
		{/if}
	</div>
</div>