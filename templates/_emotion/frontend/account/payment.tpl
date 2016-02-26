{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='ChangePaymentTitle'}{/s}", 'link'=>{url}]}
	{$sActiveAction = 'payment'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div id="center" class="grid_16 first register change_payment">

		<h1>{se name='PaymentHeadline'}Zahlungsart &auml;ndern{/se}</h1>

		{* Error messages *}
		{block name='frontend_account_payment_error_messages'}
			{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
		{/block}

		{* Payment form *}
		<form name="frmRegister" method="post" action="{url action=savePayment sTarget=$sTarget}" class="payment">

			{include file='frontend/register/payment_fieldset.tpl' form_data=$sFormData error_flags=$sErrorFlag payment_means=$sPaymentMeans}

			{block name="frontend_account_payment_action_buttons"}
			<div class="actions">
				{if $sTarget}
				<a class="button-left large left" href="{url controller=$sTarget}" title="{s name='PaymentLinkBack'}{/s}">
					{se name="PaymentLinkBack"}{/se}
				</a>
				{/if}
				<input type="submit" value="{s name='PaymentLinkSend'}{/s}" class="button-right large right" />
			</div>
			{/block}
		</form>
		<div class="space">&nbsp;</div>
	</div>
{/block}