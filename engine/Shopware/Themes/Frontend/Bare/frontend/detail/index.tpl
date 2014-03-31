{extends file='frontend/index/index.tpl'}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Custom header *}
{block name='frontend_index_header'}
	{include file="frontend/detail/header.tpl"}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div class="content block">

	{* Product navigation - Previous and next arrow button *}
	{block name="frontend_detail_index_navigation"}
		{include file="frontend/detail/navigation.tpl"}
	{/block}

	<header class="product--header block-group">

		{* Product name *}
		{block name='frontend_detail_index_name'}
			<h1 class="product--title block">{$sArticle.articleName}</h1>
		{/block}
	</header>

	{* General detailbox *}
	<div id="detailbox"><!-- detailbox -->

		{* Detailbox left *}
		<div class="left">
			<div id="img" class="grid_6 first">
				<div class="wrapper">
					{* Images *}
					{include file="frontend/detail/image.tpl"}
				</div>
			</div>
		</div>

		<div class="right"><!-- Right -->
			{* Article name *}
			{block name='frontend_detail_index_name'}
				<h1>{$sArticle.articleName}</h1>
			{/block}

			{* Detailbox middle *}
			<div id="detailbox_middle" class="grid_4">

				{* Article comments - small overview *}
				{block name="frontend_detail_comments_overview"}
					{if !{config name=VoteDisable}}
						<div class="detail_comments">
							<span class="star star{$sArticle.sVoteAverange.averange}">Star Rating</span>
							<span class="comment_numbers">(<a href="#write_comment" class="write_comment" rel="nofollow"
															  title="{s name='DetailLinkReview'}{/s}">{$sArticle.sVoteAverange.count}</a>)</span>
						</div>
					{/if}
				{/block}

				{* Additional links *}
				{block name="frontend_detail_index_actions"}
					{include file="frontend/detail/actions.tpl"}
				{/block}
			</div>



			{* Detailbox right *}

			{* Configurator table // div buybox *}
			{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}
			<div class="grid_16 first last" id="buybox">{else}
				<div class="right" id="buybox">{/if}
					<div id="detail_more"></div>

					{* Article notification *}
					{block name="frontend_detail_index_notification"}
						{if $sArticle.notification && ($sArticle.instock <= 0 || $sArticle.sVariants) && $ShowNotification}
							{include file="frontend/plugins/notification/index.tpl"}
						{/if}
					{/block}

					{* Configurator drop down menu *}
					{block name="frontend_detail_index_configurator"}
						{if $sArticle.sConfigurator}
							{if $sArticle.sConfiguratorSettings.type eq 1}
								{include file="frontend/detail/config_step.tpl"}
							{elseif $sArticle.sConfiguratorSettings.type != 2}
								{include file="frontend/detail/config_upprice.tpl"}
							{/if}
						{/if}
					{/block}

					{* Supplier name *}
					{if $sArticle.supplierName}
						{block name='frontend_detail_index_supplier'}
							<p class="supplier">{se name="DetailFromNew"}Hersteller:{/se} {$sArticle.supplierName}</p>
						{/block}
					{/if}


					{* Caching article details for future use *}
					{if $sArticle.sBundles || $sArticle.sRelatedArticles && $sArticle.crossbundlelook || $sArticle.sVariants}
						<div id="{$sArticle.ordernumber}" class="displaynone">
							{include file="frontend/detail/data.tpl" sArticle=$sArticle}
						</div>
					{/if}

					{* Caching variant article details *}
					{if $sArticle.sVariants}

						{foreach name=line from=$sArticle.sVariants item=sVariant}
							<div id="{$sVariant.ordernumber}" class="displaynone">
								{include file="frontend/detail/data.tpl" sArticle=$sVariant}
							</div>
						{/foreach}
					{/if}


					{* Article data *}
					{block name='frontend_detail_index_data'}
						{include file="frontend/detail/data.tpl" sArticle=$sArticle sView=1}
					{/block}
					{block name='frontend_detail_index_after_data'}{/block}


					{* Include buy button and quantity box *}
					{block name="frontend_detail_index_buybox"}
						{include file="frontend/detail/buy.tpl"}
					{/block}


				</div>
				<!-- //buybox -->

			</div>
			<!-- //Right -->

		</div>
		<!-- //detailbox -->


		<div class="clear">&nbsp;</div>

		{* Detailinfo *}
		{block name="frontend_detail_index_detail"}
			<div id="detailinfo">

				{* Product bundle hook point *}
				{block name="frontend_detail_index_bundle"}{/block}


				{block name="frontend_detail_index_tabs"}
					{* Tabs *}
					<div id="tabs">
						{* Tabsnavigation *}
						{include file="frontend/detail/tabs.tpl"}

						{block name="frontend_detail_index_outer_tabs"}
							<div class="inner_tabs">

								{block name="frontend_detail_index_inner_tabs"}
									{block name='frontend_detail_index_before_tabs'}{/block}

									{* Article description *}
									{block name="frontend_detail_index_tabs_description"}
										{include file="frontend/detail/description.tpl"}
									{/block}

									{* Article rating *}
									{if !{config name=VoteDisable}}
										{block name="frontend_detail_index_tabs_rating"}
											{include file="frontend/detail/comment.tpl"}
										{/block}
									{/if}

									{* Related articles *}
									{block name="frontend_detail_index_tabs_related"}
										{include file="frontend/detail/related.tpl"}
									{/block}

									{* Similar articles *}
									{include file='frontend/detail/similar.tpl'}

									{block name='frontend_detail_index_after_tabs'}{/block}
								{/block}
							</div>
						{/block}
					</div>
					<div class="detailinfo_shadow">&nbsp;</div>
					{* "Customers bought also" slider *}
					{block name="frontend_detail_index_also_bought_slider"}
						{if {config name=alsoBoughtShow}}
							{action module=widgets controller=recommendation action=bought articleId=$sArticle.articleID}
						{/if}
					{/block}

					{* "Customers similar viewed slider *}
					{block name="frontend_detail_index_similar_viewed_slider"}
						{if {config name=similarViewedShow}}
							{action module=widgets controller=recommendation action=viewed articleId=$sArticle.articleID}
						{/if}
					{/block}
					<div class="clear">&nbsp;</div>
				{/block}
			</div>
		{/block}
	</div>
{/block}