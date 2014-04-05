{namespace name="frontend/detail/index"}

<div class="product--rating-container">
	{if !{config name=VoteDisable}}
		<a href="#product--publish-comment" class="product--rating-link" rel="nofollow" title="{s name='DetailLinkReview'}{/s}" itemprop="aggregateRating"
           itemscope itemtype="http://schema.org/AggregateRating">
			{$average = $sArticle.sVoteAverange.averange / 2|round:0}
            <meta itemprop="ratingValue" content="{$average}">

			{for $value=1 to 5}
				{$cls = 'icon--star'}

				{if $value > $average}
					{$cls = 'icon--star-empty'}
				{/if}

				<i class="{$cls}"></i>
			{/for}

			{* Product rating - Comment counter *}
			{block name="frontend_detail_index_rating_label"}
				&nbsp;
                (<span itemprop="ratingCount">{$sArticle.sVoteAverange.count}</span>)
			{/block}
		</a>
	{/if}
</div>