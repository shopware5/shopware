
<div class="table_row{if $lastitem} lastrow{/if}">

	{block name='frontend_account_order_item_overview_row'}
	<div class="grid_3">
		{$offerPosition.datum|date}
	</div>

	<div class="grid_2 bold">
		{$offerPosition.ordernumber}
	</div>

	<div class="grid_3">
		{if $offerPosition.dispatch.name}
			{$offerPosition.dispatch.name}
		{else}
			{se name="OrderInfoNoDispatch"}{/se}
		{/if}
	</div>

	<div class="grid_5">
		<div class="status{$offerPosition.status}">&nbsp;</div>
		{if $offerPosition.status==0}
			{se name="OrderItemInfoNotProcessed"}{/se}
		{elseif $offerPosition.status==1}
			{se name="OrderItemInfoInProgress"}{/se}
        {elseif $offerPosition.status==2}
                {se name="OrderItemInfoCompleted"}{/se}
        {elseif $offerPosition.status==3}
                {se name="OrderItemInfoPartiallyCompleted"}{/se}
        {elseif $offerPosition.status==4}
                {se name="OrderItemInfoCanceled"}{/se}
        {elseif $offerPosition.status==5}
                {se name="OrderItemInfoReadyForShipping"}{/se}
		{elseif $offerPosition.status==6}
            {se name="OrderItemInfoPartiallyShipped"}{/se}
		{elseif $offerPosition.status==7}
            {se name="OrderItemInfoShipped"}{/se}
        {elseif $offerPosition.status==8}
            {se name="OrderItemInfoClarificationNeeded"}{/se}
		{/if}
	</div>

	<div class="grid_2">
		<div class="textright">
			<strong>
			<a href="#order{$offerPosition.ordernumber}" title="{s name='OrderActionSlide'}{/s} {$offerPosition.ordernumber}" class="orderdetails button-middle small" rel="order{$offerPosition.ordernumber}">
				{se name="OrderActionSlide"}{/se}
			</a>
			</strong>
		</div>
	</div>
	{/block}
</div>
<div id="order{$offerPosition.ordernumber}" class="displaynone">
	<div class="table">
		{block name='frontend_account_order_item_detail_table_head'}
		<div class="table_head">
			<div class="grid_8">
				{se name="OrderItemColumnName"}{/se}
			</div>
			<div class="grid_2">
				<div class="center">
					{se name="OrderItemColumnQuantity"}{/se}
				</div>
			</div>
			<div class="grid_3">
				<div class="textright">
					{se name="OrderItemColumnPrice"}{/se}
				</div>
			</div>
			<div class="grid_2">
				<div class="textright">
					{se name="OrderItemColumnTotal"}{/se}
				</div>
			</div>
		</div>
		{/block}

		<input type="hidden" name="sAddAccessories" value="{$ordernumber|escape}" />
		{foreach from=$offerPosition.details item=article}
			<div class="table_row">

				<div class="grid_8">
					{block name='frontend_account_order_item_name'}

					{* Name *}
					{if $article.modus == 10}
						<strong class="articleName">{se name='OrderItemInfoBundle'}{/se}</strong>
					{else}
						<strong class="articleName">{$article.name}</strong>
					{/if}
					{/block}

					{block name='frontend_account_order_item_unitprice'}
					{if $article.purchaseunit}
			            <div class="article_price_unit">
			                <p>
			                    <strong>{se name="OrderItemInfoContent"}{/se}:</strong> {$article.purchaseunit} {$article.sUnit.description}
			                </p>
			                {if $article.purchaseunit != $article.referenceunit}
			                    <p>
			                        {if $article.referenceunit}
			                            <strong class="baseprice">{se name="OrderItemInfoBaseprice"}{/se}:</strong> {$article.referenceunit} {$article.sUnit.description} = {$article.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
			                        {/if}
			                    </p>
			                {/if}
			            </div>
			        {/if}
			        <div class="currentPrice">
			     	{if $article.currentPrice}
			   		  	<strong>{se name="OrderItemInfoCurrentPrice"}{/se}:</strong>
			     		{if $article.currentHas_pseudoprice}
			     			<em>{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$article.currentPseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</em>
			     		{/if}
			     		{$article.currentPrice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
			     	{/if}
					</div>
					{/block}

					{block name='frontend_account_order_item_downloadlink'}
					{* If ESD-Article *}
					{if $article.esdarticle && $offerPosition.cleared|in_array:$sDownloadAvailablePaymentStatus}
						<p class="download">
							<strong>
								<a href="{$article.esdLink}">
									{se name="OrderItemInfoInstantDownload"}{/se}
								</a>
							</strong>
						</p>
					{/if}
					{/block}
				</div>

				<div class="grid_2 center">
					<div class="center">
						{block name='frontend_account_order_item_quantity'}
							{$article.quantity}
						{/block}
					</div>
				</div>

				<div class="grid_3 textright">
					<div class="textright">
						{block name='frontend_account_order_item_price'}
					    {if $article.price}
					    	{$article.price} {$offerPosition.currency_html}*
						{else}
							{se name="OrderItemInfoFree"}{/se}
						{/if}
						{/block}
					</div>
				</div>

				<div class="grid_2 bold textright">
					<div class="textright">
						<strong>
							{block name='frontend_account_order_item_amount'}
							{if $article.amount}
								{$article.amount} {$offerPosition.currency_html}*
							{else}
								{se name="OrderItemInfoFree"}{/se}
							{/if}
							{/block}
						</strong>
					</div>
				</div>
			</div>
		{/foreach}

		<div class="table_foot">
			<div class="grid_3">
				{block name="frontend_account_order_item_label_date"}
				<p>
					{* Order date *}
					<strong>{se name="OrderItemColumnDate"}{/se}</strong>
				</p>
				{/block}

				{block name="frontend_account_order_item_label_ordernumber"}
				<p>
					{* Order number *}
					<strong>{se name="OrderItemColumnId"}{/se}</strong>
				</p>
				{/block}

				{block name="frontend_account_order_item_label_dispatch"}
				{if $offerPosition.dispatch}
				<p>
					{* Shipping method *}
					<strong>{se name="OrderItemColumnDispatch"}{/se}</strong>
				</p>
				{/if}
				{/block}

				{block name="frontend_account_order_item_label_trackingcode"}
				{if $offerPosition.trackingcode}
				<p>
					{* Package tracking code *}
					<strong>{se name="OrderItemColumnTracking"}{/se}</strong>
				</p>
				{/if}
				{/block}
			</div>

			<div class="grid_3">
				{block name='frontend_account_order_item_date'}
				<p>
					{$offerPosition.datum|date}
				</p>
				{/block}

				{block name='frontend_account_order_item_ordernumber'}
				<p>
					{$offerPosition.ordernumber}
				</p>
				{/block}

				{block name='frontend_account_order_item_dispatch'}
				{if $offerPosition.dispatch}
				<p>
					{$offerPosition.dispatch.name}
				</p>
				{/if}
				{/block}

				{block name='frontend_account_order_item_trackingcode'}
				{if $offerPosition.trackingcode}
				<p>
					{if $offerPosition.dispatch.status_link}
						{eval var=$offerPosition.dispatch.status_link}
					{else}
						{$offerPosition.trackingcode}
					{/if}
				</p>
				{/if}
				{/block}
			</div>
			<div class="grid_3 push_4">
				<p class="textright">
					<strong>
					{se name="OrderItemShippingcosts"}{/se}
					</strong>
				</p>
				{if $offerPosition.taxfree}
					<p class="textright">
						<strong>
							{se name="OrderItemNetTotal"}{/se}
						</strong>
					</p>
				{else}
					<p class="bold textright">
						<strong>
							{se name="OrderItemTotal"}{/se}
						</strong>
					</p>
				{/if}
			</div>
			<div class="grid_2 push_4">
				<div class="textright">
					{block name="frontend_account_order_item_shippingamount"}
					<p class="bold">
						{$offerPosition.invoice_shipping} {$offerPosition.currency_html}
					</p>
					{/block}

					{block name="frontend_acccount_order_item_amount"}
					{if $offerPosition.taxfree}
						<p class="bold">
							{$offerPosition.invoice_amount_net} {$offerPosition.currency_html}
						</p>
					{else}
						<p class="bold">
							{$offerPosition.invoice_amount} {$offerPosition.currency_html}
						</p>
					{/if}
					{/block}
				</div>
			</div>
			<div class="clear">&nbsp;</div>

			{* Repeat order *}
			{block name="frontend_account_order_item_repeat_order"}
			<form method="post" action="{url controller='checkout' action='add_accessories'}">
				{foreach from=$offerPosition.details item=article}{if $article.modus == 0}
					<input name="sAddAccessories[]" type="hidden" value="{$article.articleordernumber|escape}" />
					<input name="sAddAccessoriesQuantity[]" type="hidden" value="{$article.quantity|escape}" />
				{/if}{/foreach}

				{if $offerPosition.activeBuyButton}
					<input type="submit" class="button-right small_right" value="{s name='OrderLinkRepeat'}{/s}" />
				{/if}
			</form>
			{/block}

			<div class="doublespace">&nbsp;</div>
			{if $offerPosition.customercomment}
				<h4 class="bold">{se name="OrderItemCustomerComment"}Ihr Kommentar{/se}</h4>
				<blockquote>
					{$offerPosition.customercomment}
				</blockquote>
			{/if}

			<div class="space">&nbsp;</div>

			{if $offerPosition.comment}
				<h4 class="bold">{se name="OrderItemComment"}Unser Kommentar{/se}</h4>
				<blockquote>
					{$offerPosition.comment}
				</blockquote>
			{/if}


		</div>
	</div>
</div>
