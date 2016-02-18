{block name="frontend_detail_tabs"}
<a id="write_comment">&nbsp;</a>
<ul>

	{block name="frontend_detail_tabs_description"}
		<li class="first">
			<a href="#description">{se name='DetailTabsDescription'}{/se}</a>
		</li>
	{/block}
	
	{block name="frontend_detail_tabs_rating"}

	{if !{config name=VoteDisable}}
		<li>
			<a href="#comments">
				<span>
					{s name='DetailTabsRating'}{/s} ({$sArticle.sVoteAverange.count})
				</span>
			</a>
		</li>
	{/if}
	{/block}
	
	{block name="frontend_detail_tabs_related"}
	{if $sArticle.sRelatedArticles && !$sArticle.crossbundlelook}
		<li>
			<a href="#related">
				{s name='DetailTabsAccessories'}Zubehör{/s} [{$sArticle.sRelatedArticles|@count}]
			</a>
		</li>
	{/if}
	{/block}
</ul>
{/block}