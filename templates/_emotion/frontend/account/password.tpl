
{extends file='frontend/index/index.tpl'}

{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="grid_20 password">

	{* Error messages *}
	{block name='frontend_account_error_messages'}
		{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
	{/block}
	
	{* Success message *}
	{if $sSuccess}
		{block name='frontend_account_password_success'}
	    <div class="success">
	    	<strong>{s name="PasswordInfoSuccess"}{/s}</strong>
	    </div>
	    <p>
	   		<a href="{url controller='account' action='password'}" class="button-left large"><span>{s name="LoginBack"}{/s}</span></a>
	    </p>
	    {/block}
	{else}
	
	{* Recover password form *}
	{block name='frontend_account_password_form'}
	<form name="frmRegister" method="post" action="{url action=password}">	    
		<h2 class="headingbox_dark largesize">{s name="PasswordHeader"}{/s}</h2>
	    <div class="outer">
	        <fieldset>
	            <p>
	                <label>{s name="PasswordLabelMail"}{/s}</label>
	                <input name="email" type="text" id="txtmail" class="text" /><br />
	            </p>
	            <p class="description">{s name="PasswordText"}{/s}</p>
	        </fieldset>
	        
	        <p class="buttons">
	            <a href="{url controller='account'}" class="button-left large">{s name="PasswordLinkBack"}{/s}</a>
	            <input type="submit" class="button-right large" value="{s name="PasswordSendAction"}Passwort anfordern{/s}" />
	            <div class="clear">&nbsp;</div>
	        </p>
	    </form>
	    </div>
	{/block}
	{/if}
</div>
<div class="doublespace">&nbsp;</div>
{/block}
