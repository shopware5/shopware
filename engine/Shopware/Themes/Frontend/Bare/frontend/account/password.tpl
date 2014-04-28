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
			{include file="frontend/_includes/messages.tpl" type="success" content="{s name='PasswordInfoSuccess'}{/s}"}
			<p>
				<a href="javascript:history.back();" class="button-left large"><span>{se name="LoginBack"}{/se}</span></a>
			</p>
	    {/block}
	{else}
	
	{* Recover password form *}
	{block name='frontend_account_password_form'}
	<form name="frmRegister" method="post" action="{url action=password}">	    
		<h2 class="headingbox_dark largesize">{se name="PasswordHeader"}{/se}</h2>
	    <div class="outer">
	        <fieldset>
	            <p>
	                <label>{se name="PasswordLabelMail"}{/se}</label>
	                <input name="email" type="text" id="txtmail" class="text" /><br />
	            </p>
	            <p class="description">{se name="PasswordText"}{/se}</p>
	        </fieldset>
	        
	        <p class="buttons">
	            <a href="javascript:history.back();" class="button-left large">{se name="PasswordLinkBack"}{/se}</a>
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