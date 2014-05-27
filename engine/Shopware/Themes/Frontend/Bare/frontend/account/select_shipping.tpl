{extends file="frontend/account/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
	{$sBreadcrumb[] = ["name"=>"{s name="SelectShippingTitle"}{/s}", "link"=>{url}]}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="account--shipping-address account--content">

		{* Shipping addresses list *}
		{block name="frontend_account_select_shipping_address"}
			<div class="account--addresses-container">
				{if $sShippingAddresses}

					{block name="frontend_account_select_shipping_headline"}
						<div class="account--welcome">
							<h1 class="panel--title">{s name="SelectShippingHeader"}{/s}</h1>
						</div>
					{/block}

					{block name="frontend_account_select_shipping_container"}
						{foreach $sShippingAddresses as $key => $sAddress}
							<div class="address--container{if $sAddress@iteration is even by 1} right{else} left{/if}">
								<form name="frmRegister" method="post" action="{url action=saveShipping}">
									<input type="hidden" name="sSelectAddress" value="{$sAddress.hash}" />
									<input type="hidden" name="sTarget" value="{$sTarget|escape}" />

									{include file="frontend/account/select_address.tpl"}
								</form>
							</div>
						{/foreach}
					{/block}

				{* if the user doesn't have any orders *}
				{else}
					{block name="frontend_account_select_shipping_info_empty"}
						{include file="frontend/_includes/messages.tpl" type="warning" content="{s name="SelectShippingInfoEmpty"}{/s}"}
					{/block}
				{/if}
			</div>
		{/block}

		{block name="frontend_account_select_shipping_action_buttons"}
			<a class="btn btn--secondary left" href="{if $sTarget}{url controller=$sTarget}{else}{url controller="account"}{/if}" title="{s name="SelectShippingLinkBack"}{/s}">
				{s name="SelectShippingLinkBack"}{/s}
			</a>
		{/block}

	</div>
{/block}