{namespace name="frontend/detail/comment"}
<div class="review--entry block-group is--answer{if $isLast} is--last{/if}">

	{* Author block *}
	{block name="frontend_detail_answer_author_block"}
		<div class="entry--author block">

			{* Answer author label *}
			{block name='frontend_detail_answer_author_label'}
				<strong class="content--label">
					{s name="DetailCommentInfoFrom"}{/s}
				</strong>
			{/block}

			{* Answer author content *}
			{block name='frontend_detail_answer_author_field'}
				<span class="content--field">
					{s name="DetailCommentInfoFromAdmin"}Admin{/s}
				</span>
			{/block}

			{* Review publish date label *}
			{block name='frontend_detail_answer_date_label'}
				<strong class="content--label">
					{s name="DetailCommentInfoAt"}Am:{/s}
				</strong>
			{/block}

			{* Review publish date content *}
			{block name='frontend_detail_answer_date_content'}
				<span class="content--field">
					{$vote.answer_date|date:"DATE_MEDIUM"}
				</span>
			{/block}
		</div>
	{/block}

	{* Answer content *}
	{block name='frontend_detail_answer_content'}
		<div class="entry--content block">
			<p class="content--box review--content">
				{$vote.answer}
			</p>
		</div>
	{/block}
</div>