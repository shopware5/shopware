{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='MyOrdersTitle'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name='frontend_index_content'}

{* Orders overview *}
<div class="grid_16 orders" id="center">
	{if !$sOpenOrders}
	{block name="frontend_account_orders_info_empty"}
		<fieldset>
			<div class="notice center bold">
				{se name="OrdersInfoEmpty"}{/se}
			</div>
		</fieldset>
	{/block}
	{else}
	{block name="frontend_account_orders_before_orders"}{/block}
	<h1>{se name="OrdersHeader"}{/se}</h1>
	<div class="orderoverview_active">
	
		<div class="table grid_16">
			{block name="frontend_account_orders_table_head"}
			<div class="table_head">
				
				<div class="grid_3">
					{se name="OrderColumnDate"}{/se}
				</div>
				
				<div class="grid_2">
					{se name="OrderColumnId"}{/se}
				</div>
				<div class="grid_3">
					{se name="OrderColumnDispatch"}{/se}
				</div>
				
				<div class="grid_5">
					{se name="OrderColumnStatus"}{/se}
				</div>
				
				<div class="grid_2 textright">
					<div class="textright">
						{se name="OrderColumnActions"}{/se}
					</div>
				</div>
			</div>
			{/block}
			{foreach name=orderitems from=$sOpenOrders item=offerPosition}
				{if $smarty.foreach.orderitems.last}
					{assign var=lastitem value=1}
				{else}
					{assign var=lastitem value=0}
				{/if}
				{include file="frontend/account/order_item.tpl" lastitem=$lastitem}
			{/foreach}
		</div>
	</div>
	{block name="frontend_account_orders_after_orders"}{/block}
	<div class="space">&nbsp;</div>
	{/if}
</div>
{/block}