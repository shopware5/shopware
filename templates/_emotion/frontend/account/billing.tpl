{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='ChangeBillingTitle'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="grid_16 register change_billing">
	
	{* Error messages *}
	{block name='frontend_account_error_messages'}
		<h1>{se name='BillingHeadline'}Rechnungsadresse ändern{/se}</h1>
  		{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
	{/block}
	
	{* Personal form *}
	<form name="frmRegister" method="post" action="{url action=saveBilling sTarget=$sTarget}">
	
	{* Personal fieldset *}
	{block name='frontend_account_personal_information'}
		{include file='frontend/register/personal_fieldset.tpl' update=true form_data=$sFormData error_flags=$sErrorFlag}
	{/block}
	
	{* Billing fieldset *}
	{block name='frontend_account_billing_information'}
		{include file='frontend/register/billing_fieldset.tpl' update=true form_data=$sFormData error_flags=$sErrorFlag country_list=$sCountryList}
	{/block}
	
	<div class="space">&nbsp;</div>
	
	{block name='frontend_account_action_buttons'}
	<div class="actions">
		{if $sTarget}
			<a class="button-left large left" href="{url controller=$sTarget}" title="{s name='BillingLinkBack'}{/s}">
				{se name="BillingLinkBack"}{/se}
			</a>
		{/if}
		<input type="submit" value="{s name='BillingLinkSend'}{/s}" class="button-right large right" />
	</div>
	{/block}
	</form>
	<div class="space">&nbsp;</div>
</div>
{/block}