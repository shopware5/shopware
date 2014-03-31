{block name="frontend_detail_tabs"}
<ul class="tab--navigation">

	{block name="frontend_detail_tabs_description"}
		<li class="navigation--entry">
			<a class="navigation--link" href="#description">{s name='DetailTabsDescription'}{/s}</a>
		</li>
	{/block}
	
	{block name="frontend_detail_tabs_rating"}
		{if !{config name=VoteDisable}}
			<li class="navigation--entry">
				<a href="#comments" class="navigation--link">{s name='DetailTabsRating'}{/s} ({$sArticle.sVoteAverange.count})</a>
			</li>
		{/if}
	{/block}
	
	{block name="frontend_detail_tabs_related"}
		{if $sArticle.sRelatedArticles && !$sArticle.crossbundlelook}
			<li class="navigation--entry">
				<a href="#related" class="navigation--link">{s name='DetailTabsAccessories'}Zubehör{/s} [{$sArticle.sRelatedArticles|@count}]</a>
			</li>
		{/if}
	{/block}
</ul>
{/block}