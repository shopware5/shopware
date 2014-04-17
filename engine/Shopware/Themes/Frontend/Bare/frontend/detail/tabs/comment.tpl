{namespace name="frontend/detail/comment"}
<div class="content--product-reviews">
	{* Response save comment *}
	{if $sAction == "ratingAction"}
		{block name='frontend_detail_comment_error_messages'}
			{if $sErrorFlag}
				<div class="error alert">
					{s name="DetailCommentInfoFillOutFields"}{/s}
				</div>
			{else}
				{if {config name="OptinVote"} && !{$smarty.get.sConfirmation}}
					<div class="success alert">
						{s name="DetailCommentInfoSuccessOptin"}{/s}
					</div>
				{else}
					<div class="success alert">
						{s name="DetailCommentInfoSuccess"}{/s}
					</div>
				{/if}
			{/if}
		{/block}
	{/if}

	{* Review title *}
	{block name="frontend_detail_tabs_rating_title"}
		<h2 class="content--title">
			{s name="DetailCommentHeader"}{/s} "{$sArticle.articleName}"
		</h2>
	{/block}

	{* Display review *}
	{if $sArticle.sVoteComments}
		{foreach $sArticle.sVoteComments as $vote}

			{* Review entry *}
			{block name="frontend_detail_comment_block"}
				{include file="frontend/detail/comment/entry.tpl" isLast=$vote@last}
			{/block}

			{* Review answer *}
            {block name="frontend_detail_answer_block"}
                {if $vote.answer}
					{include file="frontend/detail/comment/answer.tpl" isLast=$vote@last}
                {/if}
            {/block}
		{/foreach}
	{/if}

	{* Publish product review *}
	{block name='frontend_detail_comment_post'}
		{include file="frontend/detail/comment/form.tpl"}
	{/block}
</div>
