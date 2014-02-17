
{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='SelectShippingTitle'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="grid_16 addresses">
	{if $sShippingAddresses}
		<h2 class="headingbox">{se name="SelectShippingHeader"}{/se}</h2>
		<div class="inner_container">
		{foreach from=$sShippingAddresses item=sAddress key=key}
		
			<form name="frmRegister" method="post" action="{url action=saveShipping}">
				<input type="hidden" name="sSelectAddress" value="{$sAddress.hash}" />
				<input type="hidden" name="sTarget" value="{$sTarget|escape}" />
				
				{include file='frontend/account/select_address.tpl'}
			</form>
		{/foreach}
		<div class="clear">&nbsp;</div>
		</div>
		
	{* if the user doesn't have any orders *}
	{else}
		{block name="frontend_account_select_shipping_info_empty"}
		<div class="notice">
			<div class="center">
				<strong>
					{s name="SelectShippingInfoEmpty"}{/s}
				</strong>
			</div>
		</div>
		{/block}
	{/if}
	
	<div class="doublespace">&nbsp;</div>
	
	{block name="frontend_account_select_shipping_action_buttons"}
	<a class="button-left large" href="{if $sTarget}{url controller=$sTarget}{else}{url controller='account'}{/if}" title="{s name='SelectShippingLinkBack'}{/s}">
		{se name="SelectShippingLinkBack"}{/se}
	</a>
	{/block}
	
	<div class="doublespace">&nbsp;</div>
</div>
{/block}
