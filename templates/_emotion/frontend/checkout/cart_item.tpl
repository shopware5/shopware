{if $sBasketItem.modus != 1 && $sBasketItem.modus != 3 && $sBasketItem.modus != 10 && $sBasketItem.modus != 2 && $sBasketItem.modus != 4}
<div class="table_row">
	<form name="basket_change_quantity{$sBasketItem.id}" method="post" action="{url action='changeQuantity' sTargetAction=$sTargetAction}">
		{* Article informations *}
		<div class="grid_6">
			<div class="first">

                {if $sBasketItem.additional_details.sConfigurator}
                    {$detailLink={url controller="detail" sArticle=$sBasketItem.articleID number=$sBasketItem.ordernumber}}
                {else}
                    {$detailLink=$sBasketItem.linkDetails}
                {/if}

				{* Article picture *}
				{block name='frontend_checkout_cart_item_image'}
				{if $sBasketItem.image.src.0}
					<a href="{$detailLink}" title="{$sBasketItem.articlename|strip_tags}" class="thumb_image">
						<img src="{$sBasketItem.image.src.1}" border="0" alt="{$sBasketItem.articlename}" />
					</a>
				{else}
					<img class="no_image" src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{$sBasketItem.articlename}" />
				{/if}
				{/block}
				
				{block name='frontend_checkout_cart_item_details'}
				<div class="basket_details">
					{* Article name *}
					{if $sBasketItem.modus ==0}
						<a class="title" href="{$detailLink}" title="{$sBasketItem.articlename|strip_tags}">
							{$sBasketItem.articlename|strip_tags|truncate:60}
						</a>
						<p class="ordernumber">
							{se name="CartItemInfoId"}{/se} {$sBasketItem.ordernumber}
						</p>
					{else}
						{$sBasketItem.articlename}
					{/if}
					
					{block name='frontend_checkout_cart_item_details_inline'}{/block}
				</div>
				{/block}
			</div>
		</div>
		
		{* Delivery informations *}
		{block name='frontend_checkout_cart_item_delivery_informations'}
			<div class="grid_3">
				<div class="delivery">
					{if {config name=BasketShippingInfo}}
						{if $sBasketItem.shippinginfo}
							{include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sBasketItem}
			    		{/if}
			    	{else}
			    		&nbsp;
		    		{/if}
	    		</div>
			</div>
		{/block}
		
		{* Article amount *}
		{block name='frontend_checkout_cart_item_quantity'}
		<div class="grid_1">
			{if $sBasketItem.modus == 0}
				<select name="sQuantity" class="auto_submit">
				{section name="i" start=$sBasketItem.minpurchase loop=$sBasketItem.maxpurchase+1 step=$sBasketItem.purchasesteps}
					<option value="{$smarty.section.i.index}" {if $smarty.section.i.index==$sBasketItem.quantity}selected="selected"{/if}>
							{$smarty.section.i.index} 
					</option>
				{/section}
				</select>
				<input type="hidden" name="sArticle" value="{$sBasketItem.id}" />
			{else}
				&nbsp;
			{/if}
		</div>
		{/block}
		
		{* Article price *}
		{block name='frontend_checkout_cart_item_price'}
		<div class="grid_2">
			<div class="textright">
				{if !$sBasketItem.modus}{$sBasketItem.price|currency}{block name='frontend_checkout_cart_tax_symbol'}*{/block}{else}&nbsp;{/if}
			</div>
		</div>
		{/block}
		
		{* Tax price *}
		{block name='frontend_checkout_cart_item_tax_price'}{/block}
		
		{* Article total sum *}
		{block name='frontend_checkout_cart_item_total_sum'}
		<div class="grid_2">
			<div class="textright">
				<strong>
					{$sBasketItem.amount|currency}*
				</strong>
			</div>
		</div>
		{/block}
		
		{block name='frontend_checkout_cart_item_delete_article'}
		<div class="action">
			{if $sBasketItem.modus == 0}
				<a href="{url action='deleteArticle' sDelete=$sBasketItem.id sTargetAction=$sTargetAction}" class="del" title="{s name='CartItemLinkDelete '}{/s}">
					&nbsp;
				</a>
				&nbsp;
			{/if}
		</div>
		{/block}
		<div class="clear">&nbsp;</div>
	</form>
</div>

{* Voucher *}
{elseif $sBasketItem.modus == 2}
	<div class="table_row voucher">
		<div class="grid_6">
			{block name='frontend_checkout_cart_item_voucher_details'}
			<div class="voucher_img">&nbsp;</div>
			<div class="basket_details">
				<strong class="title">{$sBasketItem.articlename}</strong>
				
				<p class="ordernumber">
				{se name="CartItemInfoId"}{/se}: {$sBasketItem.ordernumber}
				</p>
			</div>
			{/block}
			<div class="clear">&nbsp;</div>
		</div>
		
		{* Tax price *}
		{block name='frontend_checkout_cart_item_voucher_tax_price'}{/block}
		
		{block name='frontend_checkout_cart_item_voucher_price'}
		<div class="grid_3 push_5">
			<div class="textright">
				<strong>
				{if $sBasketItem.itemInfo}
					{$sBasketItem.itemInfo}
				{else}
					{$sBasketItem.price|currency} {block name='frontend_checkout_cart_tax_symbol'}*{/block}
				{/if}
				</strong>
			</div>
		</div>
		{/block}

		
		{block name='frontend_checkout_cart_item_voucher_delete'}
		<div class="action">
			<a href="{url action='deleteArticle' sDelete=voucher sTargetAction=$sTargetAction}" class="del" title="{s name='CartItemLinkDelete'}{/s}">&nbsp;</a>
		</div>
		{/block}
	</div>

{* Basket rebate *}
{elseif $sBasketItem.modus == 3}
<div class="table_row rebate">

	<div class="grid_6">
		{block name='frontend_checkout_cart_item_rebate_detail'}
		<div class="basket_details">
			<strong class="title">{$sBasketItem.articlename}</strong>
		</div>
		{/block}
		<div class="clear">&nbsp;</div>
	</div>
	
	{* Tax price *}
	{block name='frontend_checkout_cart_item_rebate_tax_price'}{/block}
	
	{block name='frontend_checkout_cart_item_rebate_price'}
	<div class="grid_3 push_5">
		<div class="textright">
			<strong>
				{if $sBasketItem.itemInfo}
					{$sBasketItem.itemInfo}
				{else}
					{$sBasketItem.price|currency} {block name='frontend_checkout_cart_tax_symbol'}*{/block}
				{/if}
			</strong>
		</div>
		<div class="clear">&nbsp;</div>
	</div>
	{/block}
	
</div>

{* Selected premium article *}
{elseif $sBasketItem.modus == 1}
	<div class="table_row selected_premium">
		<div class="grid_6">
			{block name='frontend_checkout_cart_item_premium_image'}
			{if $sBasketItem.image.src.0}
				<a class="thumb_image">
					<img src="{$sBasketItem.image.src.1}" border="0" alt="{$sBasketItem.articlename} "/>
				</a>
			{else}
				<span class="premium_img">
					{se name="sCartItemFree"}GRATIS!{/se}
				</span>
			{/if}
			{/block}
			
			{block name='frontend_checkout_cart_item_premium_details'}
			<div class="basket_details">
				<strong class="title">{$sBasketItem.articlename}</strong>
			
				<p class="thankyou">
					{s name="CartItemInfoPremium"}{/s}
				</p>
			</div>
			{/block}
			<div class="clear">&nbsp;</div>
		</div>
		
		{* Tax price *}
		{block name='frontend_checkout_cart_item_premium_tax_price'}{/block}
		
		{block name='frontend_checkout_cart_item_premium_price'}
		<div class="grid_3 push_5">
			<div class="textright">
				<strong>
					{s name="CartItemInfoFree"}{/s}
				</strong>
			</div>
			<div class="clear">&nbsp;</div>
		</div>
		{/block}
		
		{block name='frontend_checkout_cart_item_premium_delete'}
		<div class="action">
			<a href="{url action='deleteArticle' sDelete=$sBasketItem.id sTargetAction=$sTargetAction}" class="del" title="{s name='CartItemLinkDelete'}{/s}">&nbsp;</a>
		</div>
		{/block}
	</div>

{* Extra charge for small quantities *}
{elseif $sBasketItem.modus == 4}
	<div class="table_row small_quantities">
		{block name='frontend_checkout_cart_item_small_quantities_details'}
		<div class="grid_6">
			<div class="basket_details">
				<strong class="title">{$sBasketItem.articlename}</strong>
			</div>
			<div class="clear">&nbsp;</div>
		</div>
		{/block}
		
		{* Tax price *}
		{block name='frontend_checkout_cart_item_small_quantites_tax_price'}{/block}
		
		{block name='frontend_checkout_Cart_item_small_quantities_price'}
		<div class="grid_3 push_5">
			<div class="textright">
				<strong>
					{if $sBasketItem.itemInfo}
						{$sBasketItem.itemInfo}
					{else}
						{$sBasketItem.price|currency} {block name='frontend_checkout_cart_tax_symbol'}*{/block}
					{/if}
				</strong>
			</div>
			<div class="clear">&nbsp;</div>
		</div>
		{/block}
		
	</div>
	
{* Bundle discount price *}
{elseif $sBasketItem.modus == 10}
	<div class="table_row bundle_row">
		
		{block name='frontend_checkout_cart_item_bundle_details'}
		<div class="grid_6">
			<div class="basket_details">
				<strong class="title">{s name='CartItemInfoBundle'}{/s}</strong>
			</div>
			<div class="clear">&nbsp;</div>
		</div>
		{/block}
		
		{* Tax price *}
		{block name='frontend_checkout_cart_item_bundle_tax_price'}{/block}
		
		{block name='frontend_checkout_cart_item_bundle_price'}
		<div class="grid_3 push_5">
			<div class="textright">
				<strong>
					{$sBasketItem.amount|currency} {block name='frontend_checkout_cart_tax_symbol'}*{/block}
				</strong>
			</div>
			<div class="clear">&nbsp;</div>
		</div>
		{/block}
	</div>
{/if}