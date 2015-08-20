{extends file="frontend/account/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
	{$sBreadcrumb[] = ["name"=>"{s name="SelectBillingTitle"}{/s}", "link"=>{url}]}
{/block}

{* Back to the shop button *}
{block name='frontend_index_logo_trusted_shops' append}
	{if $theme.checkoutHeader}
		<a href="{url controller='index'}"
		   class="btn is--small btn--back-top-shop is--icon-left"
		   title="{s name='FinishButtonBackToShop' namespace='frontend/checkout/finish'}{/s}">
			<i class="icon--arrow-left"></i>
			{s name="FinishButtonBackToShop" namespace="frontend/checkout/finish"}{/s}
		</a>
	{/if}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="content account--billing-address account--content">

		{* Billing addresses list *}
		{block name="frontend_account_select_billing_address"}
            {block name="frontend_account_select_billing_headline"}
                <div class="account--welcome">
                    <h1 class="panel--title">{s name="SelectBillingHeader"}{/s}</h1>
                </div>
            {/block}

			<div class="account--addresses-container">
                {if $sBillingAddresses}
					{block name="frontend_account_select_billing_container"}
						{foreach $sBillingAddresses as $key => $sAddress}
							<div class="address--container">
								<form name="frmRegister" method="post" action="{url action=saveBilling}">
									<input type="hidden" name="sSelectAddress" value="{$sAddress.hash}" />
									<input type="hidden" name="sTarget" value="{$sTarget|escape}" />

                                    {block name="frontend_account_select_billing_address_fieldset"}
									    {include file="frontend/account/select_address.tpl"}
                                    {/block}
								</form>
							</div>
						{/foreach}
					{/block}

				{* if the user doesn't have any orders *}
				{else}
					{block name="frontend_account_select_billing_info_empty"}
						{include file="frontend/_includes/messages.tpl" type="warning" content="{s name="SelectBillingInfoEmpty"}{/s}"}
					{/block}
				{/if}
			</div>
		{/block}

		{block name="frontend_account_select_billing_action_buttons"}
            <div class="panel--actions billing--actions is--wide">
                <a class="btn is--secondary" href="{if $sTarget}{url controller=$sTarget}{else}{url controller="account"}{/if}" title="{"{s name="SelectBillingLinkBack"}{/s}"|escape}">
                    {s name="SelectBillingLinkBack"}{/s}
                </a>
            </div>
		{/block}

	</div>
{/block}