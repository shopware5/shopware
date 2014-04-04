{block name="frontend_detail_data"}

	{if !$sArticle.liveshoppingData.valid_to_ts}
		{* Graduated prices *}
		{if $sArticle.sBlockPrices && ($sArticle.sConfiguratorSettings.type!=2 || $sArticle.pricegroupActive) && !$sArticle.liveshoppingData.valid_to_ts}

			{* Include block prices *}
			{block name="frontend_detail_data_block_price_include"}
				{include file="frontend/detail/block_price.tpl" sArticle=$sArticle}
			{/block}

			{* Product price info *}
			{block name='frontend_detail_data_price_info'}
				<p class="buybox--info" data-modal="true">
					{s namespace="frontend/detail/data" name="DetailDataPriceInfo"}{/s}
				</p>
			{/block}

			{if $sArticle.purchaseunit}
				{* Article price *}
				{block name='frontend_detail_data_price'}
					<div class='article_details_price_unit'>
						<span>
							<strong>{s name="DetailDataInfoContent"}{/s}</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
							{if $sArticle.purchaseunit != $sArticle.referenceunit}
								<span class="smallsize">
									{if $sArticle.referenceunit}
										({$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$sArticle.referenceunit} {$sArticle.sUnit.description})
									{/if}
								</span>
							{/if}
						</span>
					</div>
				{/block}
			{/if}

		{else}
			{if $sArticle.sConfiguratorSettings.type!=2}

				<div class="product--price price--default{if $sArticle.pseudoprice} price--discount{/if}">

					{* Discount price *}
					{block name='frontend_detail_data_pseudo_price'}
						{if $sArticle.pseudoprice}

							{* Discount price content *}
							{block name='frontend_detail_data_pseudo_price_discount_content'}
								<strong class="price--content content--discount">
									{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} <span class="price--line-through"{$sArticle.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}

									{* Percentage discount *}
									{block name='frontend_detail_data_pseudo_price_discount_content_percentage'}
										{if $sArticle.pseudopricePercent.float}
											({$sArticle.pseudopricePercent.float} % {s name="DetailDataInfoSavePercent"}{/s})
										{/if}
									{/block}
								</strong>
							{/block}
						{/if}
					{/block}

					{* Default price *}
					{block name='frontend_detail_data_price_configurator'}
						{if $sArticle.priceStartingFrom && !$sArticle.sConfigurator && $sView}

							{* Price - Starting from *}
							{block name='frontend_detail_data_price_configurator_starting_from_content'}
								<strong class="price--content content--starting-from">
									{s name="DetailDataInfoFrom"}{/s} {$sArticle.priceStartingFrom|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
								</strong>
							{/block}
						{else}

							{* Regular price *}
							{block name='frontend_detail_data_price_default'}
							<strong class="price--content content--default">
                                <meta itemprop="price" content="{$sArticle.price|replace:',':'.'}">
								{if $sArticle.priceStartingFrom && !$sArticle.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$sArticle.price|currency}{s name="Star"}*{/s}
							</strong>
							{/block}
						{/if}
					{/block}
				</div>

				{* Unit price *}
				{if $sArticle.purchaseunit}
					{block name='frontend_detail_data_price'}
						<div class='product--price price--unit'>

							{* Unit price label *}
							{block name='frontend_detail_data_price_unit_label'}
								<strong class="price--label label--purchase-unit">
									{s name="DetailDataInfoContent"}{/s}
								</strong>
							{/block}

							{* Unit price content *}
							{block name='frontend_detail_data_price_unit_content'}
								{$sArticle.purchaseunit} {$sArticle.sUnit.description}
							{/block}

							{* Unit price is based on a reference unit *}
							{if $sArticle.purchaseunit && $sArticle.purchaseunit != $sArticle.referenceunit}

								{* Reference unit price content *}
								{block name='frontend_detail_data_price_unit_reference_content'}
									({$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
									/ {$sArticle.referenceunit} {$sArticle.sUnit.description})
								{/block}
							{/if}
						</div>
					{/block}
				{/if}

				{* Tax information *}
				{block name='frontend_detail_data_price_info'}
					<p class="product--tax" data-modal-link="true">
						{s name="DetailDataPriceInfo"}{/s}
					</p>
				{/block}
			{/if}
		{/if}
	{/if}

	{if $sArticle.sBlockPrices && (!$sArticle.sConfigurator || $sArticle.pricegroupActive) && $sArticle.sConfiguratorSettings.type!=2}
		{foreach from=$sArticle.sBlockPrices item=row key=key}
			{if $row.from=="1"}
				<input id="price_{$sArticle.ordernumber}" type="hidden" value="{$row.price|replace:",":"."}">
			{/if}
		{/foreach}
	{/if}

	{block name="frontend_detail_data_delivery"}
		{* Delivery informations *}
		{include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sArticle}
	{/block}

	{* @deprecated Liveshopping data *}
	{block name="frontend_detail_data_liveshopping"}{/block}
{/block}
