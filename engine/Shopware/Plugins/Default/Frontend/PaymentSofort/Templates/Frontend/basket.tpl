{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-07-09 11:10:01 +0200 (Mon, 09 Jul 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0  $Id: basket.tpl 4656 2012-07-09 09:10:01Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}
<div id="finished">
	{if $sOrderNumber || $sTransactionumber}
		<div class="orderdetails">
			{* Invoice number *}
			{block name='frontend_checkout_finish_invoice_number'}
			{if $sOrderNumber}
				<p class="bold">{se name="FinishInfoId"}{/se} {$sOrderNumber}</p>
			{/if}
			{/block}
			
			{* Transaction number *}
			{block name='frontend_checkout_finishs_transaction_number'}
			{if $sTransactionumber}
				<p>{se name="FinishInfoTransaction"}{/se} {$sTransactionumber}</p>
			{/if}
		    {/block}
		</div>
		<div class="space">&nbsp;</div>
	{/if}
    
    <div class="table">
	    {* Table header *}
	    {include file="frontend/checkout/finish_header.tpl"}
		
		{* Article items *}
		{foreach name=basket from=$sBasket.content item=sBasketItem key=key}
			{include file='frontend/checkout/finish_item.tpl'}	
		{/foreach}
					
		{* Table footer *}
		<!--{include file="frontend/checkout/finish_footer.tpl"}-->
	</div>
</div>
<div class="doublespace">&nbsp;</div>