{* Tab navigation for the product detail page *}
{block name="frontend_detail_tabs"}
	<ul class="tab--navigation">

		{* Tab navigation - Description *}
		{block name="frontend_detail_tabs_description"}
			<li class="navigation--entry">
				<a class="navigation--link" href="#content--description">{s name='DetailTabsDescription'}{/s}</a>
			</li>
		{/block}

		{* Tab navigation - Product reviews *}
		{block name="frontend_detail_tabs_rating"}
			{if !{config name=VoteDisable}}
				<li class="navigation--entry">
					<a href="#content--product-reviews" class="navigation--link">{s name='DetailTabsRating'}{/s} ({$sArticle.sVoteAverange.count})</a>
				</li>
			{/if}
		{/block}

		{* Tab navigation - Related products *}
		{block name="frontend_detail_tabs_related"}
			{if $sArticle.sRelatedArticles && !$sArticle.crossbundlelook}
				<li class="navigation--entry">
					<a href="#content--related-products" class="navigation--link">{s name='DetailTabsAccessories'}Zubehör{/s} [{$sArticle.sRelatedArticles|@count}]</a>
				</li>
			{/if}
		{/block}
	</ul>
{/block}