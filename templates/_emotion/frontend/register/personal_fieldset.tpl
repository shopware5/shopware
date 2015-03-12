<div class="personal_settings">
	<h2 class="headingbox_dark largesize">{s name='RegisterPersonalHeadline'}{/s}</h2>
	
	{* Customer type *}
	{block name='frontend_register_personal_fieldset_customer_type'}
		{if $form_data.sValidation}
			<input type="hidden" name="register[personal][sValidation]" value="{$form_data.sValidation|escape}" />
		{elseif {config name=showCompanySelectField}}
			<div>
				<label for="register_personal_customer_type">{s name='RegisterPersonalLabelType'}{/s}*:</label>
				<select id="register_personal_customer_type" name="register[personal][customer_type]">
					<option value="private"{if $form_data.customer_type eq "private"} selected="selected"{/if}>{s name='RegisterPersonalLabelPrivate'}{/s}</option>
					<option value="business"{if $form_data.customer_type eq "business" or $form_data.company or $form_data.sValidation} selected="selected"{/if}>{s name='RegisterPersonalLabelBusiness'}{/s}</option>
				</select>
			</div>
		{else}
			<input type="hidden" id="register_personal_customer_type" name="register[personal][customer_type]" value="private" />
		{/if}
	{/block}
	
	{* Salutation *}
	{block name='frontend_register_personal_fieldset_salutation'}
		<div class="salutation">
			<label>{s name='RegisterLabelSalutation'}{/s}</label>
			<span class="{if $error_flags.salutation}instyle_error{/if}">
			<input class="radio" id="register_personal_salutation_mr" type="radio" name="register[personal][salutation]" value="mr" {if $form_data.salutation eq "mr"}checked="checked"{else}checked="checked"{/if} /> <label for="register_personal_salutation_mr">{s name='RegisterLabelMr'}{/s}</label>
			<input class="radio" id="register_personal_salutation_ms" type="radio" name="register[personal][salutation]" value="ms" {if $form_data.salutation eq "ms"}checked="checked"{/if} /> <label for="register_personal_salutation_ms">{s name='RegisterLabelMs'}{/s}</label>
			</span>
			<div class="clear">&nbsp;</div>
		</div>
	{/block}
	
	{* Firstname *}
	{block name='frontend_register_personal_fieldset_input_firstname'}		
		<div>
			<label for="firstname">{se name='RegisterLabelFirstname'}{/se}</label>
			<input autocomplete="section-personal given-name" name="register[personal][firstname]" type="text" id="firstname" value="{$form_data.firstname|escape}" class="text required {if $error_flags.firstname}instyle_error{/if}" />
		</div>
	{/block}
	
	{* Lastname *}
	{block name='frontend_register_personal_fieldset_input_lastname'}
		<div>
			<label for="lastname">{se name='RegisterLabelLastname'}{/se}</label>
			<input autocomplete="section-personal family-name" name="register[personal][lastname]" type="text"  id="lastname" value="{$form_data.lastname|escape}" class="text required {if $error_flags.lastname}instyle_error{/if}" />
		</div>
	{/block}
	
	{* Skip login *}
	{if !$update}
		{block name='frontend_register_personal_fieldset_skip_login'}
			{if !$sEsd && !$form_data.sValidation && !{config name=NoAccountDisable}}
		        <div class="check">
		            <input type="checkbox" value="1" id="register_personal_skipLogin" name="register[personal][skipLogin]" class="chkbox" {if $form_data.skipLogin||$form_data.accountmode || $skipLogin}checked {/if}/>
		            <label for="register_personal_skipLogin" class="chklabel"><strong>{se name='RegisterLabelNoAccount'}{/se}</strong></label>
		            <div class="clear">&nbsp;</div>	
		        </div>
		    {/if}
	    {/block}
	    
		{* E-Mail *}
		{block name='frontend_register_personal_fieldset_input_mail'}
			<div>
			    <label for="register_personal_email">
			    	{se name='RegisterLabelMail'}{/se}
			    </label>
			    <input autocomplete="section-personal email" name="register[personal][email]" type="text" id="register_personal_email" value="{$form_data.email|escape}" class="text required email {if $error_flags.email}instyle_error{/if}" />
			</div>
		
			{if {config name=DOUBLEEMAILVALIDATION}}
			    <div>
			        <label for="register_personal_emailConfirmation">
			        	{se name='RegisterLabelMailConfirmation'}{/se}
			        </label>
			        <input autocomplete="section-personal email" name="register[personal][emailConfirmation]" type="text" id="register_personal_emailConfirmation" value="{$form_data.emailConfirmation|escape}" class="text emailConfirmation required {if $error_flags.emailConfirmation}instyle_error{/if}" />
			    </div>
			{/if}
		{/block}
	{/if}
		
	{if !$update}
		{* Password *}
		{block name='frontend_register_personal_fieldset_input_password'}
			<div class="fade_password">
				<label for="register_personal_password">{se name='RegisterLabelPassword'}{/se}</label>
				<input name="register[personal][password]" type="password" id="register_personal_password" class="text required password {if $error_flags.password}instyle_error{/if}" />
			</div>
		{/block}
	
		{* Password confirmation *}
		{block name='frontend_register_personal_fieldset_input_password_confirm'}
            {if {config name=doublePasswordValidation}}
                <div class="fade_password">
                    <label for="register_personal_passwordConfirmation">{se name='RegisterLabelPasswordRepeat'}{/se}</label>
                    <input name="register[personal][passwordConfirmation]"  type="password" id="register_personal_passwordConfirmation" class="text required passwordConfirmation {if $error_flags.passwordConfirmation}instyle_error{/if}" />
                </div>
            {/if}
		{/block}
	
		{* Password description *}
		{block name='frontend_register_personal_fieldset_password_description'}
			<div class="fade_password description">
				{se name='RegisterInfoPassword'}{/se} {config name=MinPassword} {se name='RegisterInfoPasswordCharacters'}{/se}<br /> {se name='RegisterInfoPassword2'}{/se}
			</div>
		{/block}
	{/if}
	
	{* Phone *}
	{block name='frontend_register_personal_fieldset_input_phone'}
        {if {config name=showPhoneNumberField}}
            <div>
                <label for="phone" {if !{config name=requirePhoneField}}class="normal"{/if}>{se name='RegisterLabelPhone'}{/se}{if {config name=requirePhoneField}}*{/if}:</label>
                <input autocomplete="section-personal tel" name="register[personal][phone]" type="text" id="phone" value="{$form_data.phone|escape}" class="text {if {config name=requirePhoneField}}required{/if} {if $error_flags.phone && {config name=requirePhoneField}}instyle_error{/if}" />
            </div>
        {/if}
	{/block}
		
	{* Birthday *}
    {if {config name=showBirthdayField} && !$update}
        {block name='frontend_register_personal_fieldset_birthday'}
            <div id="birthdate">
                <label for="register_personal_birthdate" {if !{config name=requireBirthdayField}}class="normal"{/if}>{s name='RegisterLabelBirthday'}{/s}{if {config name=requireBirthdayField}}*{/if}:</label>
                <select id="register_personal_birthdate" name="register[personal][birthday]" class="{if {config name=requireBirthdayField}}required{/if} {if $error_flags.birthday && {config name=requireBirthdayField}}instyle_error{/if}">
                    <option value="">{s name='RegisterBirthdaySelectDay'}day{/s}</option>
                    {section name="birthdate" start=1 loop=32 step=1}
                        <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                    {/section}
                </select>

                <select name="register[personal][birthmonth]" class="{if {config name=requireBirthdayField}}required{/if} {if $error_flags.birthmonth && {config name=requireBirthdayField}}instyle_error{/if}">
                    <option value="">{s name='RegisterBirthdaySelectMonth'}month{/s}</option>
                    {section name="birthmonth" start=1 loop=13 step=1}
                        <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                    {/section}
                </select>

                <select name="register[personal][birthyear]" class="{if {config name=requireBirthdayField}}required{/if} {if $error_flags.birthyear && {config name=requireBirthdayField}}instyle_error{/if}">
                    <option value="">{s name='RegisterBirthdaySelectYear'}year{/s}</option>
                    {section name="birthyear" loop={$smarty.now|date_format:"%Y"} max={$smarty.now|date_format:"%Y"}-1900 step=-1}
                        <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                    {/section}
                </select>

                <div class="clear"></div>
            </div>
        {/block}
    {/if}
</div>
