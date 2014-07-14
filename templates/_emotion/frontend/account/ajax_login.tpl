{* Heading *}
<div class="ajax_login_form">
<div class="heading">
	<h2>{se name='LoginHeader'}{/se}</h2>
	
	{* Close button *}
	<a href="#" class="modal_close" title="{s name='LoginActionClose'}{/s}">
		{s name='LoginActionClose'}{/s}
	</a>
</div>

{* Error messages *}
{block name='frontend_account_ajax_login_error_messages'}
	{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
{/block}
<fieldset>

	<div class="new_customer">
		<h2>{se name="LoginLabelNew"}{/se}</h2>
		
		<form method="get" name="new_customer" class="new_customer_form" action="{url controller='register'}">
		<p>
			{se name="LoginInfoNew"}{/se}
		</p>
		<div class="clear"></div>
		{if !{config name=NoAccountDisable}}
		<div class="checkbox">
				<input type="checkbox" class="chk_noaccount" id="skipLogin" name="skipLogin" value="1" />
				<label class="chklabel" for="skipLogin">{se name="LoginLabelNoAccount"}Kein Kundenkonto erstellen{/se}</label>
		</div>
		{/if}

		<input type="submit" class="button-right large left" value="{s name='LoginActionCreateAccount'}Weiter{/s}" />
		
		</form>
		<div class="clear">&nbsp;</div>
	</div>
	
	<form method="post" name="existing_customer" action="{url action=login}">
	<div class="existing_customer">
		<h2>{se name="LoginLabelExisting"}{/se}</h2>
		<input type="hidden" name="accountmode" value="2" />
		
		<p>
			{se name="LoginTextExisting"}{/se}
		</p>
		
		{block name='frontend_account_ajax_login_input_email'}
		<div>
			<label for="email">{se name="LoginLabelMail"}{/se}</label>
	    	<input name="email" type="text" tabindex="1" value="{$sFormData.email|escape}" id="email" class="text {if $sErrorFlag.email}instyle_error{/if}" />
	    	<div class="clear">&nbsp;</div>
		</div>
		{/block}
		
		{block name='frontend_account_ajax_login_input_password'}
		<div>
			<label for="ajax_login_password">{se name="LoginLabelPassword"}Ihr Password{/se}</label>
			<input name="password" type="password" tabindex="2" id="ajax_login_password" class="text password {if $sErrorFlag.password}instyle_error{/if}" /><br />
			<a class="lostpassword" href="{url action=password}" title="{s name='LoginLinkLostPassword'}{/s}">{se name='LoginLinkLostPassword'}{/se}</a>
			<div class="clear">&nbsp;</div>
		</div>
		{/block}
		
		{block name='frontend_account_ajax_login_action_buttons'}
		<div class="last">
			<input type="submit" class="button-middle large bold" id="checkout_button" value="{s name='LoginActionNext'}Einloggen{/s}" name="Submit" />
		</div>
		{/block}
		
	</div>	
	</form>
</fieldset>
</div>