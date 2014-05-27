{extends file="frontend/account/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
	{$sBreadcrumb[] = ["name"=>"{s name="ChangeShippingTitle"}{/s}", "link"=>{url}]}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="account--change-shipping account--content register--content">

		{* Shipping headline *}
		{block name="frontend_account_shipping_headline"}
			<div class="account--welcome">
				<h1 class="panel--title">{s name="ShippingHeadline"}{/s}</h1>
			</div>
		{/block}

		{block name="frontend_account_shipping_content"}
			<div class="panel has--border">

				{* Error messages *}
				{block name="frontend_account_shipping_error_messages"}
					{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
				{/block}

				{* Shipping form *}
				{block name="frontend_account_shipping_form"}
					<div class="account--shipping-form">
						<form name="frmRegister" method="post" action="{url action=saveShipping sTarget=$sTarget}">

							{* Shipping fieldset *}
							{block name="frontend_account_shipping_fieldset"}
								{include file="frontend/register/shipping_fieldset.tpl" form_data=$sFormData error_flags=$sErrorFlag country_list=$sCountryList}
							{/block}

							{* Shipping actions *}
							{block name="frontend_account_shipping_action_buttons"}
								<div class="account--actions">
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

			</div>
		{/block}

	</div>
{/block}