{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='ChangeShippingTitle'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="grid_16 register change_shipping">

	{* Error messages *}
	{block name='frontend_account_shipping_error_messages'}
		<h1>{se name='ShippingHeadline'}Lieferadresse ändern{/se}</h1>
  {include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
	{/block}

	{* Shipping form *}
	<form name="frmRegister" method="post" action="{url action=saveShipping sTarget=$sTarget}">
	
	{* Shipping fieldset *}
	{block name='frontend_account_shipping_fieldset'}
		{include file='frontend/register/shipping_fieldset.tpl' form_data=$sFormData error_flags=$sErrorFlag country_list=$sCountryList}
	{/block}
	
	<div class="space">&nbsp;</div>
	
	{block name="frontend_account_shipping_action_buttons"}
	<div class="actions">
		{if $sTarget}
			<a class="button-left large left" href="{url controller=$sTarget}" title="{s name='ShippingLinkBack'}{/s}">
				{se name="ShippingLinkBack"}{/se}
			</a>
		{/if}
		<input type="submit" value="{s name='ShippingLinkSend'}{/s}" class="button-right large right" />
	</div>
	{/block}
	</form>
	<div class="space">&nbsp;</div>
</div>
{/block}