{block name='frontend_register_personal_fieldset_input_phone' append}

	{if $piKlarnaCountryIso != "DE" && ($sUserData["additional"]["payment"]["name"] == "KlarnaInvoice"  || $sUserData["additional"]["payment"]["name"] == "KlarnaPartPayment")}

		<div>

        {if $piKlarnaCountryIso == "NL"}

			<label for="phone" class="KlarnaRegisterHouseExtLabel">{$pi_Klarna_lang['houseExt']}</label>

        {else}

            <label for="phone" class="KlarnaRegisterSocialNrLabel">{$pi_Klarna_lang['SocialNr']}</label>

        {/if}

			<input name="register[personal][additional]" type="text" id="text4" value="{$form_data.text4|escape}" class="text required {if $error_flags.text4}instyle_error{/if}" />

		</div>

	{/if}

	{if ($sUserData["additional"]["payment"]["name"] == "KlarnaInvoice" || $sUserData["additional"]["payment"]["name"] == "KlarnaPartPayment")

		&& ($piKlarnaCountryIso == "DE" || $piKlarnaCountryIso == "NL")}

		<div id="birthdate">

			<label for="register_personal_birthdate" class="KlarnaRegisterBirthdayLabel">{$pi_Klarna_lang['birthday']}</label>

			<select id="register_personal_birthdate" name="register[personal][birthdayRate]">

			<option value="">--</option>

			{section name="birthdate" start=1 loop=32 step=1}

				<option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>

			{/section}

			</select>



			<select name="register[personal][birthmonthRate]">

			<option value="">-</option>

			{section name="birthmonth" start=1 loop=13 step=1}

				<option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>

			{/section}

			</select>



			<select name="register[personal][birthyearRate]">

			<option value="">----</option>

			{section name="birthyear" loop=2000 max=100 step=-1}

				<option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>

			{/section}

			</select>

		</div>

    {/if}



{/block}



