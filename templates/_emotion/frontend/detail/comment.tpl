{extends file='parent:frontend/detail/comment.tpl'}

{block name='frontend_detail_comment_author'}

	<div class="star star{$vote.points*2}"></div>

	<strong class="author">
		{se name="DetailCommentInfoFrom"}{/se} <span class="name">{$vote.name}</span>
	</strong>
{/block}

{* Star rating *}
{block name="frontend_detail_comment_star_rating"}{/block}

	{block name='frontend_detail_comment_post'}

		{* Display notice if the shop owner needs to unlock a comment before it will'be listed *}
		{if {config name=VoteUnlock}}
			<div class="notice">
				<span>{s name='DetailCommentTextReview'}{/s}</span>
			</div>
		{/if}

		{* Write comment *}
		<h2 class="headingbox_dark">
			{se name="DetailCommentHeaderWriteReview"}{/se}
		</h2>
		<form method="post" action="{url action='rating' sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}">
			<div>
				<a name="tabbox"></a>

				<fieldset>
					{* Name *}
					{block name='frontend_detail_comment_input_name'}
					<div>
						<label for="sVoteName">{se name="DetailCommentLabelName"}{/se}*: </label>
						<input name="sVoteName" type="text" id="sVoteName" value="{$sFormData.sVoteName|escape}" class="text {if $sErrorFlag.sVoteName}instyle_error{/if}" />
						<div class="clear">&nbsp;</div>
					</div>
					{/block}

					{* E-Mail address *}
					{if {config name=OptinVote} == true}
						{block name='frontend_detail_comment_input_mail'}
						<div>
							<label for="sVoteMail">{se name="DetailCommentLabelMail"}{/se}*: </label>
							<input name="sVoteMail" type="text" id="sVoteMail" value="{$sFormData.sVoteMail|escape}" class="text {if $sErrorFlag.sVoteMail}instyle_error{/if}" />
							<div class="clear">&nbsp;</div>
						</div>
						{/block}
					{/if}

					{* Comment summary*}
					{block name='frontend_detail_comment_input_summary'}
					<div>
						<label for="sVoteSummary">{se name="DetailCommentLabelSummary"}{/se}*:</label>
						<input name="sVoteSummary" type="text" value="{$sFormData.sVoteSummary|escape}" id="sVoteSummary" class="text {if $sErrorFlag.sVoteSummary}instyle_error{/if}" />
						<div class="clear">&nbsp;</div>
					</div>
					{/block}

					{* Star Rating *}
					{block name='frontend_detail_comment_input_rating'}
					<div>
						<label for="sVoteStars">{se name="DetailCommentLabelRating"}{/se}*:</label>
						<select name="sVoteStars" class="normal" id="sVoteStars">
							<option value="10">{s name="Rate10"}{/s}</option>
							<option value="9">{s name="Rate9"}{/s}</option>
							<option value="8">{s name="Rate8"}{/s}</option>
							<option value="7">{s name="Rate7"}{/s}</option>
							<option value="6">{s name="Rate6"}{/s}</option>
							<option value="5">{s name="Rate5"}{/s}</option>
							<option value="4">{s name="Rate4"}{/s}</option>
							<option value="3">{s name="Rate3"}{/s}</option>
							<option value="2">{s name="Rate2"}{/s}</option>
							<option value="1">{s name="Rate1"}{/s}</option>
						</select>
						<div class="clear">&nbsp;</div>
					</div>
					{/block}

					{* Comment text *}
					{block name='frontend_detail_comment_input_text'}
					<div>
						<label for="sVoteComment">{se name="DetailCommentLabelText"}{/se}</label>
						<textarea name="sVoteComment" id="sVoteComment" cols="3" rows="2" class="text {if $sErrorFlag.sVoteComment}instyle_error{/if}">{$sFormData.sVoteComment|escape}</textarea>
						<div class="clear">&nbsp;</div>
					</div>
					{/block}

					{* Captcha *}
					{block name='frontend_detail_comment_input_captcha'}
					<div class="captcha">
                        <div class="captcha-placeholder" data-src="{url module=widgets controller=Captcha action=refreshCaptcha}"></div>
						<div class="code">
							<label>{se name="DetailCommentLabelCaptcha"}{/se}</label>
							<input type="text" name="sCaptcha"class="text {if $sErrorFlag.sCaptcha}instyle_error{/if}" />
							<div class="clear">&nbsp;</div>
						</div>
					</div>
					{/block}
					<div class="clear">&nbsp;</div>
					<p>
						{se name="DetailCommentInfoFields"}{/se}
					</p>
				</fieldset>

				<div class="buttons">
					<input class="button-right large" type="submit" name="Submit" value="{s name="DetailCommentActionSave"}{/s}"/>

					<div class="clear">&nbsp;</div>
				</div>
			</div>
		</form>
	{/block}
