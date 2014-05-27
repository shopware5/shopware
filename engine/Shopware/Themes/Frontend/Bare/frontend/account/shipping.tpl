{extends file="frontend/account/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
	{$sBreadcrumb[] = ["name"=>"{s name="ChangeShippingTitle"}{/s}", "link"=>{url}]}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="account--content register--content shipping--content">

		{* Error messages *}
		{block name="frontend_account_shipping_error_messages"}
			<h1 class="panel--title">{s name="ShippingHeadline"}Lieferadresse ändern{/s}</h1>

			{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
		{/block}

		{* Shipping form *}
		{block name="frontend_account_shipping_form"}
			<form name="frmRegister" method="post" action="{url action=saveShipping sTarget=$sTarget}">
		{/block}

		{* Shipping fieldset *}
		{block name="frontend_account_shipping_fieldset"}
			{include file="frontend/register/shipping_fieldset.tpl" form_data=$sFormData error_flags=$sErrorFlag country_list=$sCountryList}
		{/block}

		{block name="frontend_account_shipping_action_buttons"}
			<div class="actions">
				{if $sTarget}
					<a class="btn btn--secondary left" href="{url controller=$sTarget}" title="{s name="ShippingLinkBack"}{/s}">
						{s name="ShippingLinkBack"}{/s}
					</a>
				{/if}
				<input type="submit" value="{s name="ShippingLinkSend"}{/s}" class="btn btn--primary right" />
			</div>
		{/block}

		</form>

	</div>
{/block}