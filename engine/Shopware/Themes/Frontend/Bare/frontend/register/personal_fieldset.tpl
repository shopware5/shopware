<div class="panel register--personal">
	<h2 class="panel--title underline">{s name='RegisterPersonalHeadline'}{/s}</h2>
	<div class="panel--body">
		{* Customer type *}
		{block name='frontend_register_personal_fieldset_customer_type'}
			{if $form_data.sValidation}
				<input type="hidden" name="register[personal][sValidation]" value="{$form_data.sValidation|escape}" />
			{else}
				<div class="field--select personal--field">
					<span class="arrow"></span>
					<select id="register_personal_customer_type" name="register[personal][customer_type]">
						<option>{s name='RegisterPersonalLabelType'}{/s}*</option>
						<option value="private"{if $form_data.customer_type eq "private"} selected="selected"{/if}>{s name='RegisterPersonalLabelPrivate'}{/s}</option>
						<option value="business"{if $form_data.customer_type eq "business" or $form_data.company or $form_data.sValidation} selected="selected"{/if}>{s name='RegisterPersonalLabelBusiness'}{/s}</option>
					</select>
				</div>
			{/if}
		{/block}

		{* Salutation *}
		{block name='frontend_register_personal_fieldset_salutation'}
			<div class="field--select">
				<span class="arrow"></span>
				<select name="register[personal][salutation]" id="salutation" class="normal {if $error_flags.salutation}instyle_error{/if}">
					<option>{s name='RegisterLabelSalutation'}{/s}</option>
					<option value="mr" {if $form_data.salutation eq "mr"}selected="selected"{/if}>{s name='RegisterLabelMr'}{/s}</option>
					<option value="ms" {if $form_data.salutation eq "ms"}selected="selected"{/if}>{s name='RegisterLabelMs'}{/s}</option>
				</select>
			</div>
		{/block}

		{* Firstname *}
		{block name='frontend_register_personal_fieldset_input_firstname'}
			<div class="register--firstname">
				<input name="register[personal][firstname]" type="text" placeholder="{s name='RegisterLabelFirstname'}{/s}" id="firstname" value="{$form_data.firstname|escape}" class="register--field required {if $error_flags.firstname}instyle_error{/if}" />
			</div>
		{/block}

		{* Lastname *}
		{block name='frontend_register_personal_fieldset_input_lastname'}
			<div class="register--lastname">
				<input name="register[personal][lastname]" type="text" placeholder="{s name='RegisterLabelLastname'}{/s}" id="lastname" value="{$form_data.lastname|escape}" class="register--field required {if $error_flags.lastname}instyle_error{/if}" />
			</div>
		{/block}

		{* Skip login *}
		{if !$update}
			{block name='frontend_register_personal_fieldset_skip_login'}
				{if !$sEsd && !$form_data.sValidation && !{config name=NoAccountDisable}}
					<div class="register--check">
						<input type="checkbox" value="1" id="register_personal_skipLogin" name="register[personal][skipLogin]" class="register--checkbox chkbox" {if $form_data.skipLogin||$form_data.accountmode || $skipLogin}checked {/if}/>
						<label for="register_personal_skipLogin" class="chklabel"><strong>{s name='RegisterLabelNoAccount'}{/s}</strong></label>
					</div>
				{/if}
			{/block}

			{* E-Mail *}
			{block name='frontend_register_personal_fieldset_input_mail'}
				<div class="register--email">
					<input name="register[personal][email]" type="text" placeholder="{s name='RegisterLabelMail'}{/s}" id="register_personal_email" value="{$form_data.email|escape}" class="register--field required email {if $error_flags.email}instyle_error{/if}" />
				</div>

				{if {config name=DOUBLEEMAILVALIDATION}}
					<div class="register--emailconfirm">
						<input name="register[personal][emailConfirmation]" type="text" placeholder="{s name='RegisterLabelMailConfirmation'}{/s}" id="register_personal_emailConfirmation" value="{$form_data.emailConfirmation|escape}" class="register--field emailConfirmation required {if $error_flags.emailConfirmation}instyle_error{/if}" />
					</div>
				{/if}
			{/block}
		{/if}

		{if !$update}
			{* Password *}
			{block name='frontend_register_personal_fieldset_input_password'}
				<div class="register--password">
					<input name="register[personal][password]" type="password" placeholder="{s name='RegisterLabelPassword'}{/s}" id="register_personal_password" class="register--field required password {if $error_flags.password}instyle_error{/if}" />
				</div>
			{/block}

			{* Password confirmation *}
			{block name='frontend_register_personal_fieldset_input_password_confirm'}
				{if {config name=doublePasswordValidation}}
				<div class="register--password">
					<input name="register[personal][passwordConfirmation]" type="password" placeholder="{s name='RegisterLabelPasswordRepeat'}{/s}" id="register_personal_passwordConfirmation" class="register--field required passwordConfirmation {if $error_flags.passwordConfirmation}instyle_error{/if}" />
				</div>
				{/if}
			{/block}

			{* Password description *}
			{block name='frontend_register_personal_fieldset_password_description'}
				<div class="register--password description">
					{s name='RegisterInfoPassword'}{/s}{config name=MinPassword} {s name='RegisterInfoPasswordCharacters'}{/s} {s name='RegisterInfoPassword2'}{/s}
				</div>
			{/block}
		{/if}

		{* Phone *}
		{block name='frontend_register_personal_fieldset_input_phone'}
			{if {config name=showPhoneNumberField}}
			<div class="register--phone">
				<input name="register[personal][phone]" type="text" placeholder="{s name='RegisterLabelPhone'}{/s}" id="phone" value="{$form_data.phone|escape}" class="register--field {if !{config name=requirePhoneField}}normal{/if}{if {config name=requirePhoneField}}required{/if} {if $error_flags.phone && {config name=requirePhoneField}}instyle_error{/if}" />
			</div>
			{/if}
		{/block}

		{* Birthday *}
		{if {config name=showBirthdayField}}
		{if !$form_data.skipLogin && !$update}
			{block name='frontend_register_personal_fieldset_birthday'}
				<div id="birthdate">
					<label for="register_personal_birthdate" class="birthday--label qnormal">{s name='RegisterLabelBirthday'}{/s}</label>

					<div class="register--birthday field--select">
						<span class="arrow"></span>
						<select id="register_personal_birthdate" name="register[personal][birthday]">
							<option value="">--</option>
							{section name="birthdate" start=1 loop=32 step=1}
								<option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
							{/section}
						</select>
					</div>
					<div class="register--birthday field--select">
						<span class="arrow"></span>
						<select name="register[personal][birthmonth]">
							<option value="">-</option>
							{section name="birthmonth" start=1 loop=13 step=1}
								<option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
							{/section}
						</select>
					</div>
					<div class="register--birthday field--select">
						<span class="arrow"></span>
						<select name="register[personal][birthyear]">
							<option value="">----</option>
							{section name="birthyear" loop=2000 max=100 step=-1}
								<option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
							{/section}
						</select>
					</div>
				</div>
			{/block}
		{/if}
		{/if}
	</div>
</div>
