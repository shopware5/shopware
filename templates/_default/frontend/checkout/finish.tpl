{extends file="frontend/checkout/confirm.tpl"}

{block name='frontend_index_content_top'}{/block}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name="frontend_index_content"}
<div class="grid_20 finish" id="center">
	{block name='frontend_checkout_finish_teaser'}

	<div class="teaser">
		<h2 class="center">{se name="FinishHeaderThankYou"}{/se}{$sShopname}!</h2>

	    <p class="center">
	    	{se name="FinishInfoConfirmationMail"}{/se}<br />{s name="FinishInfoPrintOrder"}{/s}
	    </p>

	    <div class="right">
		    <a href="#" class="button-right large" onclick="self.print()" title="{s name='FinishLinkPrint'}{/s}">
		    	{s name="FinishLinkPrint"}{/s}
		    </a>
	    </div>
	    <div class="clear">&nbsp;</div>
	</div>

	<div class="doublespace">&nbsp;</div>
	{* Trusted shops form *}
	{if {config name=TSID}}
		{include file="frontend/plugins/trusted_shops/form.tpl"}
	{/if}

	{/block}

	<div class="doublespace">&nbsp;</div>

	{block name='frontend_checkout_finish_header_items'}
		<h2 class="headingbox">{se name="FinishHeaderItems"}{/se}</h2>
	{/block}

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
                {block name='frontend_checkout_finish_item'}
                {include file='frontend/checkout/finish_item.tpl'}
                {/block}
			{/foreach}

			{* Table footer *}
			{include file="frontend/checkout/finish_footer.tpl"}
		</div>
	</div>
	<div class="doublespace">&nbsp;</div>
</div>
{/block}
