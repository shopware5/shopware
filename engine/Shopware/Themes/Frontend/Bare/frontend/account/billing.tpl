{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='ChangeBillingTitle'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="account--change-billing register--content panel right">

	<h1 class="panel--title account--headline">{s name='BillingHeadline'}{/s}</h1>
	<div class="panel has--border">

		{* Error messages *}
		{block name='frontend_account_error_messages'}
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

		{block name='frontend_account_action_buttons'}
		<div class="account--action">
			{if $sTarget}
				<a class="btn btn--secondary left" href="{url controller=$sTarget}" title="{s name='BillingLinkBack'}{/s}">
					{se name="BillingLinkBack"}{/se}
				</a>
			{/if}
			<input type="submit" value="{s name='BillingLinkSend'}{/s}" class="btn btn--primary right" />
		</div>
		{/block}
		</form>
	</div>
</div>
{/block}