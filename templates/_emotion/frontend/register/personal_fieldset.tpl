{extends file='parent:frontend/register/personal_fieldset.tpl'}

{block name='frontend_register_personal_fieldset_birthday'}
	<div id="birthdate">
		<label for="register_personal_birthdate" class="normal">{s name='RegisterLabelBirthday'}{/s}</label>
		<select id="register_personal_birthdate" name="register[personal][birthday]">
		<option value="">--</option>	
		{section name="birthdate" start=1 loop=32 step=1}
			<option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
		{/section}
		</select>
		
		<select name="register[personal][birthmonth]">
		<option value="">-</option>	
		{section name="birthmonth" start=1 loop=13 step=1}
			<option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
		{/section}
		</select>
		
		<select name="register[personal][birthyear]">
		<option value="">----</option>	
		{section name="birthyear" loop=2000 max=100 step=-1}
			<option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
		{/section}
		</select>
		<div class="clear"></div>
	</div>
{/block}