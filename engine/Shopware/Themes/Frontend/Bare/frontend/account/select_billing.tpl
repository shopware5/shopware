{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='SelectBillingTitle'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div class="account--addresses right">

		{* Billing addresses list *}
		{block name='frontend_account_select_address_container'}
			<div class="account--addresses-container">
				{if $sBillingAddresses}
					<div class="account--welcome">
						<h1 class="panel--title">{s name="SelectBillingHeader"}{/s}</h1>
					</div>
					{foreach $sBillingAddresses as $key => $sAddress}
						<div class="address--container{if $sAddress@iteration is even by 1} right{else} left{/if}">
							<form name="frmRegister" method="post" action="{url action=saveBilling}">
								<input type="hidden" name="sSelectAddress" value="{$sAddress.hash}" />
								<input type="hidden" name="sTarget" value="{$sTarget|escape}" />

								{include file='frontend/account/select_address.tpl'}
							</form>
						</div>
					{/foreach}

				{* if the user doesn't have any orders *}
				{else}
					{block name='frontend_account_select_billing_info_empty'}
						{include file="frontend/_includes/messages.tpl" type="warning" content="{s name='SelectBillingInfoEmpty'}{/s}"}
					{/block}
				{/if}
			</div>
		{/block}

		{block name='frontend_account_select_billing_action_buttons'}
				<a class="btn btn--secondary" href="{if $sTarget}{url controller=$sTarget}{else}{url controller='account'}{/if}" title="{s name='SelectBillingLinkBack'}{/s}">
					{s name="SelectBillingLinkBack"}{/s}
				</a>
		{/block}

	</div>
{/block}