{namespace name="frontend/detail/index"}

{if !{config name=VoteDisable}}
	<div class="product--rating-container">
		{include file="frontend/_includes/rating.tpl"
		voteAverage=$sArticle.sVoteAverange.averange
		voteCount=$sArticle.sVoteAverange.count
		voteType=5}
	</div>
{/if}