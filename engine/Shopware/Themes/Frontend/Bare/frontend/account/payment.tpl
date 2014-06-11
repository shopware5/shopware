{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='ChangePaymentTitle'}{/s}", 'link'=>{url}]}
	{$sActiveAction = 'payment'}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="content block account--content">

		{* Error messages *}
		{block name="frontend_account_payment_error_messages"}
			{if $sErrorMessages}
				<div class="account--error">
					{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
				</div>
			{/if}
		{/block}

		{* Welcome text *}
		{block name="frontend_account_payment_welcome"}
			<div class="account--welcome panel">
				{block name="frontend_account_payment_welcome_headline"}
					<h1 class="panel--title">{s name="PaymentHeadline"}Zahlungsart &auml;ndern{/s}</h1>
				{/block}
			</div>
		{/block}

		{* Payment form *}
		{block name="frontend_account_payment_form"}
			<form name="frmRegister" method="post" action="{url action=savePayment sTarget=$sTarget sTargetAction=$sTargetAction|default:"index"}" class="payment">

				{block name="frontend_account_payment_form_content"}
					{include file='frontend/register/payment_fieldset.tpl' form_data=$sFormData error_flags=$sErrorFlag payment_means=$sPaymentMeans}
				{/block}

				{block name="frontend_account_payment_action_buttons"}
					<div class="panel--actions">
						{if $sTarget}
							<a class="btn btn--secondary" href="{url controller=$sTarget action=$sTargetAction|default:"index"}" title="{s name='PaymentLinkBack'}{/s}">
								{s name="PaymentLinkBack"}{/s}
							</a>
						{/if}
						<input type="submit" value="{s name='PaymentLinkSend'}{/s}" class="btn btn--primary" />
					</div>
				{/block}
			</form>
		{/block}

	</div>
{/block}