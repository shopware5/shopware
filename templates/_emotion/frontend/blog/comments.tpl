
<div class="doublespace">&nbsp;</div>
<div id="commentcontainer">

	{* Comments headline *}
	{if $sArticle.comments}
		{block name='frontend_blog_comments_comments_headline'}
		<h2 class="headingbox">
			{$sArticle.comments|count} {se name="BlogInfoComments"}{/se}
		</h2>
		{/block}
	{/if}

	{* List comments *}
	{if $sArticle.comments}
		<ul>
			{foreach name=comments from=$sArticle.comments item=vote}

				{block name='frontend_blog_comments_comment_block'}

				<li class="{cycle values='white,grey'}">
					<div class="comment_outer{if $smarty.foreach.comments.last} last{/if}">
					<div class="comment_left">

						{* Author *}
						{block name='frontend_blog_comments_comment_author'}
						<span class="author">{$vote.name}</span> <span class="date">{$vote.creationDate|date:date_long}</span>
						{/block}

						{* Stars *}
						<span class="star star{$vote.points}">{$vote.points}</span>
					</div>

					<div class="comment_right">

						{* Headline *}
						{block name='frontend_blog_comments_comment_headline'}
						<p class="hline">{$vote.headline}</p>
						{/block}

						{* Comment *}
						{block name='frontend_blog_comments_comment_text'}
						<div class="comment">{$vote.comment|nl2br}</div>
						{/block}
					</div>
					<div class="clear">&nbsp;</div>
					</div>
				</li>
				{/block}
			{/foreach}
		</ul>
	{/if}
	{if $sAction == "rating"}
		<div class="doublespace">&nbsp;</div>
		<div>
			{* Output errors *}
			{block name='frontend_blog_comments_error_messages'}
			{if $sErrorFlag}
				<div class="error">
					{se name="BlogInfoFailureFields"}{/se}
				</div>
			{else}
				{if {config name=OptInVote} && !{$smarty.get.sConfirmation} && !{$userLoggedIn}}
					<div class="success">{se name="BlogInfoSuccessOptin"}{/se}</div>
				{else}
					<div class="success">{se name="BlogInfoSuccess"}{/se}</div>
				{/if}
			{/if}
			{/block}
		</div>
	{/if}

	<div class="doublespace">&nbsp;</div>
	<h2 class="headingbox_dark">{se name="BlogHeaderWriteComment"}{/se}</h2>
	<form method="post" action="{url controller=blog action=rating blogArticle=$sArticle.id}" class="comments">

		{* Name *}
		{block name='frontend_blog_comments_input_name'}
		<p class="col">
			<label for="sCommentName">{se name="BlogLabelName"}{/se}*:</label>
			<input name="name" type="text" id="sCommentName" value="{$sFormData.name|escape}" class="text{if $sErrorFlag.name} instyle_error{/if}" />
		</p>
		{/block}

		{* E-Mail *}
        {block name='frontend_blog_comments_input_mail'}
        <p class="col">
            <label for="sCommentMail">{se name="BlogLabelMail"}{/se}{if {config name=OptInVote}}*{/if}:</label>
            <input name="eMail" type="text" id="sCommentMail" value="{$sFormData.eMail|escape}" class="text{if $sErrorFlag.eMail} instyle_error{/if}" />
        </p>
        {/block}

		{* Voting *}
		{block name='frontend_blog_comments_input_voting'}
		<p class="col">
			<label for="sVoteStars">{s name="BlogLabelRating"}{/s}*:</label>
			<select name="points" class="text" id="sVoteStars">
				<option value="10">{s name="rate10"}{/s}</option>
				<option value="9">{s name="rate9"}{/s}</option>
				<option value="8">{s name="rate8"}{/s}</option>
				<option value="7">{s name="rate7"}{/s}</option>
				<option value="6">{s name="rate6"}{/s}</option>
				<option value="5">{s name="rate5"}{/s}</option>
				<option value="4">{s name="rate4"}{/s}</option>
				<option value="3">{s name="rate3"}{/s}</option>
				<option value="2">{s name="rate2"}{/s}</option>
				<option value="1">{s name="rate1"}{/s}</option>
			</select>
		</p>
		{/block}

		{* Summary *}
		{block name='frontend_blog_comments_input_summary'}
		<p class="col">
			<label for="sCommentHeadline">{se name="BlogLabelSummary"}{/se}*:</label>
			<input name="headline" type="text" value="{$sFormData.headline|escape}" id="sCommentHeadline" class="text {if $sErrorFlag.headline}instyle_error{/if}" />
		</p>
		{/block}

		{* Opinion *}
		{block name='frontend_blog_comments_input_comment'}
		<p class="textarea">
			<label for="sComment">{se name="BlogLabelComment"}{/se}*</label>
			<textarea name="comment" id="sComment" class="text {if $sErrorFlag.comment}instyle_error{/if}" rows="5" cols="5">{$sFormData.comment|escape}</textarea>
		</p>
		{/block}

		{* Captcha *}
		{block name='frontend_blog_comments_input_captcha'}
		<div class="captcha">
			<div class="grid_4 first last">
                <div class="captcha-placeholder" data-src="{url module=widgets controller=Captcha action=refreshCaptcha}"></div>
            </div>
			<div class="grid_4 first last">
				<label>{se name="BlogLabelCaptcha"}{/se}</label>
				<input type="text" name="sCaptcha" class="text{if $sErrorFlag.sCaptcha} instyle_error{else} instyle{/if}" />
			</div>
		</div>
		{/block}
		<div class="clear">&nbsp;</div>

		{* Submit button *}
		<input class="button-right large" type="submit" name="Submit" value="{s name='BlogLinkSaveComment'}{/s}" />

		<p class="desc">
			{se name="BlogInfoFields"}{/se}
		</p>

	</form>
	<div class="doublespace">&nbsp;</div>
</div>
