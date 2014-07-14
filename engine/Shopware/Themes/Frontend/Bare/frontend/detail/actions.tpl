{block name='frontend_detail_actions_notepad'}
	<a href="{url controller='note' action='add' ordernumber=$sArticle.ordernumber}" class="action--link link--notepad" rel="nofollow" title="{s name='DetailLinkNotepad'}{/s}">
		<i class="icon--heart"></i> {s name="DetailLinkNotepad"}{/s}
	</a>
{/block}

{block name='frontend_detail_actions_review'}
	{if !{config name=VoteDisable}}
		<a href="#product--publish-comment" class="action--link link--publish-comment" rel="nofollow" title="{s name='DetailLinkReview'}{/s}">
			<i class="icon--star"></i> {s name="DetailLinkReview"}{/s}
		</a>
	{/if}
{/block}

{block name='frontend_detail_actions_voucher'}
	{if {config name=showTellAFriend}}
		<a href="{$sArticle.linkTellAFriend}" rel="nofollow" title="{s name='DetailLinkVoucher'}{/s}" class="action--link link--tell-a-friend">
			{s name="DetailLinkVoucher"}{/s}
		</a>
	{/if}
{/block}