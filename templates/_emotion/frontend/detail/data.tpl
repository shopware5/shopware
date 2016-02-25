{block name="frontend_detail_data"}

	{* Rich snippet data couldn't be in the "head"-element due to the fact that the "#detail" element needs the schema of the data *}
	{block name="frontend_detail_rich_snippets"}

		{* Supplier name *}
		{block name="frontend_detail_rich_snippets_brand"}
			<meta itemprop="brand" content="{$sArticle.supplierName|escape}" />
		{/block}

		{* Product name *}
		{block name="frontend_detail_rich_snippets_name"}
			<meta itemprop="name" content="{$sArticle.articleName|escape}" />
		{/block}

		{* Product image *}
		{block name="frontend_detail_rich_snippets_image"}
			<meta itemprop="image" content="{if $sArticle.image.src.3}{$sArticle.image.src.3}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" />
		{/block}

		{* Product description *}
		{block name="frontend_detail_rich_snippets_description"}
			<meta itemprop="description" content="{$sArticle.description_long|strip_tags|escape}" />
		{/block}

		{* Category path. Google recommend the following format: "Parent category > Child category" *}
		{block name="frontend_detail_rich_snippets_category"}
			<meta itemprop="category" content="{strip}
				{foreach $sBreadcrumb as $crumb}
					{if !$crumb@last}
						{if !$crumb@first} > {/if}{$crumb.name}
					{/if}
				{/foreach}
				{/strip}" />
		{/block}

		{* Product identifier e.g the order number *}
		{block name="frontend_detail_rich_snippets_identifier"}
			<meta itemprop="identifier" content="sku:{$sArticle.ordernumber}" />
		{/block}

		{* Currency of the price, needs to follow the ISO-4217 standard *}
		{block name="frontend_detail_rich_snippets_currency"}
			<meta itemprop="currency" content="{$Shop->getCurrency()->getCurrency()}" />
		{/block}

		{* Product price *}
		{block name="frontend_detail_rich_snippets_price"}
			<meta itemprop="price" content="{$sArticle.price}" />
		{/block}

		{* Seller of the product e.g. our shop *}
		{block name="frontend_detail_rich_snippets_seller"}
			<meta itemprop="seller" content="{$sShopname}" />
		{/block}

		{* Availability of the product *}
		{block name="frontend_detail_rich_snippets_availability"}
			<meta itemprop="availability" content="{if $sArticle.instock > 0}in_stock{else}out_of_stock{/if}" />
		{/block}

		{* Available quantity of the product *}
		{block name="frontend_detail_rich_snippets_quantity"}
			<meta itemprop="quantity" content="{$sArticle.instock}" />
		{/block}

		{* Offer url for the product similar to the canonical tag in the "head"-element *}
		{block name="frontend_detail_rich_snippets_offerUrl"}
			<meta itemprop="offerUrl" content="{url sArticle=$sArticle.articleID title=$sArticle.articleName}" />
		{/block}

		{* Aggregated review if we're dealing with more than one vote *}
		{if $sArticle.sVoteAverange.count > 1}
			{block name="frontend_detail_rich_snippets_review_aggregate"}
				<span itemprop="review" itemscope itemtype="http://data-vocabulary.org/Review-aggregate">

					{* Name of the reviewed product *}
					{block name="frontend_detail_rich_snippets_review_aggregate_name"}
						<meta itemprop="v:itemreviewed" content="{$sArticle.articleName}" />
					{/block}

					{* Minimum points *}
					{block name="frontend_detail_rich_snippets_review_aggregate_worst"}
						<meta itemprop="worst" content="0" />
					{/block}

					{* Maximum points *}
					{block name="frontend_detail_rich_snippets_review_aggregate_best"}
						<meta itemprop="best" content="10" />
					{/block}

					{* Average rating *}
					{block name="frontend_detail_rich_snippets_review_aggregate_rating"}
						<meta itemprop="rating" content="{$sArticle.sVoteAverange.averange}" />
					{/block}

					{* Vote count *}
					{block name="frontend_detail_rich_snippets_review_aggregate_count"}
						<meta itemprop="count" content="{$sArticle.sVoteAverange.count}" />
					{/block}
				</span>
			{/block}
		{elseif $sArticle.sVoteAverange.count == 1}
			{block name="frontend_detail_rich_snippets_review_single"}
				{$vote=$sArticle.sVoteComments.0}

                <span itemprop="review" itemscope itemtype="http://data-vocabulary.org/Review">

                    {* Name of the reviewed product *}
                    {block name="frontend_detail_comment_rich_snippets_name"}
                        <meta itemprop="itemreviewed" content="{$sArticle.articleName|escape}" />
                    {/block}

                    {* Review date, needs to follow the iso date format *}
                    {block name="frontend_detail_comment_rich_snippets_date"}
                        <meta itemprop="dtreviewed" content="{$vote.datum|substr:0:-9}" />
                    {/block}

                    {* Vote rating *}
                    {block name="frontend_detail_comment_rich_snippets_rating"}
                        <meta itemprop="rating" content="{$vote.points*2|replace:'.':','}" />
                    {/block}

                    {* Vote count *}
                    {block name="frontend_detail_comment_rich_snippets_count"}
                        <meta itemprop="count" content="{$sArticle.sVoteAverange.count}" />
                    {/block}

                    {* Minimum points *}
                    {block name="frontend_detail_comment_rich_snippets_worst"}
                        <meta itemprop="worst" content="0" />
                    {/block}

                    {* Maximum points *}
                    {block name="frontend_detail_comment_rich_snippets_best"}
                        <meta itemprop="best" content="10" />
                    {/block}

                    {* Review summary *}
                    {block name="frontend_detail_comment_rich_snippets_summary"}
                        <meta itemprop="summary" content="{$vote.headline|escape}" />
                    {/block}

                    {* Name of the reviewer *}
                    {block name="frontend_detail_comment_rich_snippets_reviewer"}
                        <meta itemprop="reviewer" content="{$vote.name|escape}" />
                    {/block}

                    {* Review text *}
                    {block name="frontend_detail_comment_rich_snippets_description"}
                        <meta itemprop="description" content="{$vote.comment|escape}" />
                    {/block}

                </span>
			{/block}
		{/if}
	{/block}

	{* Caching instock status *}
	{if !$sView}
		<input id='instock_{$sArticle.ordernumber}' type='hidden' value='{$sArticle.instock}' />
	{/if}

	{if $sArticle.sBlockPrices && (!$sArticle.sConfigurator || $sArticle.pricegroupActive)}
		{foreach from=$sArticle.sBlockPrices item=row key=key}
			{if $row.from=="1"}
				<input id='price_{$sArticle.ordernumber}'type='hidden' value='{$row.price|replace:",":"."}' />
			{/if}
		{/foreach}
	{else}
		{if !$sView}
			<input id='price_{$sArticle.ordernumber}' type='hidden' value='{$sArticle.price|replace:".":""|replace:",":"."}' />
		{/if}
	{/if}

	{* Order number *}
	{if $sArticle.ordernumber}
		{block name='frontend_detail_data_ordernumber'}
			<p>{se name="DetailDataId"}{/se} {$sArticle.ordernumber}</p>
		{/block}
	{/if}

	{* Attributes fields *}
	{block name='frontend_detail_data_attributes'}
		{if $sArticle.attr1}
			<p>{$sArticle.attr1}</p>
		{/if}
		{if $sArticle.attr2}
			<p>{$sArticle.attr2}</p>
		{/if}
	{/block}

	{block name="frontend_detail_data_delivery"}
		{* Delivery informations *}
		{if ($sArticle.sConfiguratorSettings.type != 1 && $sArticle.sConfiguratorSettings.type != 2) || $activeConfiguratorSelection == true}
            {include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sArticle}
        {/if}
	{/block}

	{if !$sArticle.liveshoppingData.valid_to_ts}
		{* Graduated prices *}
		{if $sArticle.sBlockPrices && !$sArticle.liveshoppingData.valid_to_ts}

            {* Include block prices *}
            {block name="frontend_detail_data_block_price_include"}
                {include file="frontend/detail/block_price.tpl" sArticle=$sArticle}
            {/block}

            {* Article price *}
            {block name='frontend_detail_data_price_info'}
                <p class="modal_open">
                    {s namespace="frontend/detail/data" name="DetailDataPriceInfo"}{/s}
                </p>
            {/block}

            {if $sArticle.purchaseunit}
            {* Article price *}
                {block name='frontend_detail_data_price'}
                <div class='article_details_price_unit'>
                            <span>
                                <strong>{se name="DetailDataInfoContent"}{/se}</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
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
			{* Pseudo price *}
			<div class='article_details_bottom'>
				{if $sArticle.purchaseunit}
					{* Article price *}
					{block name='frontend_detail_data_price'}
					<div class='article_details_price_unit'>

						<span>
							<strong>{se name="DetailDataInfoContent"}{/se}</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
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

				<div {if $sArticle.has_pseudoprice} class='article_details_price2'>{else} class='article_details_price'>{/if}
					{block name='frontend_detail_data_pseudo_price'}
					{if $sArticle.has_pseudoprice}
					{* if $sArticle.sVariants || $sArticle.priceStartingFrom*}
					<div class="PseudoPrice{if $sArticle.sVariants} displaynone{/if}">
						<em>{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$sArticle.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</em>
						{if $sArticle.pseudopricePercent.float}
							<span>
								({$sArticle.pseudopricePercent.float|number}% {se name="DetailDataInfoSavePercent"}{/se})
							</span>
						{/if}
					</div>
					{*/if*}
					{/if}
					{/block}

					{* Article price configurator *}
					{block name='frontend_detail_data_price_configurator'}
					<strong {if $sArticle.priceStartingFrom && $sView} class="starting_price"{/if}>
						{if $sArticle.priceStartingFrom && !$sArticle.sConfigurator && $sView}
							<span id="DetailDataInfoFrom">{se name="DetailDataInfoFrom"}{/se}</span>
							{$sArticle.priceStartingFrom|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
						{else}
							{$sArticle.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
						{/if}
					</strong>
					{/block}
				</div>

				{* Article price *}
				{block name='frontend_detail_data_price_info'}
				<p class="tax_attention modal_open">
					{s name="DetailDataPriceInfo"}{/s}
				</p>
				{/block}
			</div>
		{/if}
	{/if}

	{block name="frontend_detail_data_liveshopping"}
		{* Liveshopping *}
		{if $sArticle.liveshoppingData.valid_to_ts}
			{if $sArticle.liveshoppingData.typeID == 2 || $sArticle.liveshoppingData.typeID == 3}
				{include file="frontend/detail/liveshopping/detail_countdown.tpl" sLiveshoppingData=$sArticle.liveshoppingData}
			{else}
				{include file="frontend/detail/liveshopping/detail.tpl" sLiveshoppingData=$sArticle.liveshoppingData sArticlePseudoprice=$sArticle.pseudoprice}
			{/if}
		{/if}
	{/block}
{/block}
