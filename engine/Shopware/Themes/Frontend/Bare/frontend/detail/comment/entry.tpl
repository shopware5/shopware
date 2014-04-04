{namespace name="frontend/detail/comment"}

<div class="review--entry block-group{if $isLast} is--last{/if}{if $vote.answer} has--answer{/if}" itemprop="review" itemscope itemtype="http://schema.org/Review">

	<div class="entry--author block">

		{* Star rating *}
		{block name="frontend_detail_comment_star_rating"}
			<div class="product--rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
				{$average = $vote.points * 2|round:0}

                <meta itemprop="worstRating" content="1">
                <meta itemprop="ratingValue" content="{$vote.points}">
                <meta itemprop="bestRating" content="5">

				{for $value=1 to 5}
					{$cls = 'icon--star'}

					{if $value > $average}
						{$cls = 'icon--star-empty'}
					{/if}

					<i class="{$cls}"></i>
				{/for}
			</div>
		{/block}

		{* Review author *}
		{block name='frontend_detail_comment_author'}

			{* Author label *}
			{block name='frontend_detail_comment_author_label'}
				<strong class="content--label">
					{s name="DetailCommentInfoFrom"}{/s}
				</strong>
			{/block}

			{* Author content *}
			{block name='frontend_detail_comment_author_content'}
				<span class="content--field" itemprop="author">{$vote.name}</span>
			{/block}
		{/block}

		{* Review publish date *}
		{block name='frontend_detail_comment_date'}

			{* Review publish date label *}
			{block name='frontend_detail_comment_date_label'}
				<strong class="content--label">
					{s name="DetailCommentInfoAt"}Am:{/s}
				</strong>
			{/block}

			{* Review publish date content *}
			{block name='frontend_detail_comment_date_content'}
                <meta itemprop="datePublished" content="{$vote.datum|date_format:'%Y-%m-%d'}">
				<span class="content--field">
					{$vote.datum|date:"DATE_MEDIUM"}
				</span>
			{/block}
		{/block}
	</div>

	{* Review content - Title and content *}
	{block name='frontend_detail_comment_text'}
		<div class="entry--content block">

			{* Headline *}
			{block name='frontend_detail_comment_headline'}
				<h4 class="content--title" itemprop="name">
					{$vote.headline}
				</h4>
			{/block}

			{* Review text *}
			{block name='frontend_detail_comment_content'}
				<p class="content--box review--content" itemprop="reviewBody">
					{$vote.comment|nl2br}
				</p>
			{/block}
		</div>
	{/block}
</div>