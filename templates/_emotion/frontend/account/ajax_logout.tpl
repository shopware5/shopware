
{* Heading *}
<div class="ajax_login_form">
<div class="heading">
	<h2>{se name='AccountLogoutHeader'}{/se}</h2>
	
	{* Close button *}
	<a href="#" class="modal_close" title="{s name='LoginActionClose'}{/s}">
		{s name='LoginActionClose'}{/s}
	</a>
</div>
<fieldset>
<div class="logout">
{block name='frontend_account_ajax_logout_box'}
	<h2>
		{se name='AccountLogoutText'}{/se}
	</h2>
	<div class="clear">&nbsp;</div>
	<a class="button-right large right" href="{url controller='index'}" title="{s name='AccountLogoutButton'}{/s}">{se name="AccountLogoutButton"}{/se}</a>
{/block}
</div>
</fieldset>
</div>
