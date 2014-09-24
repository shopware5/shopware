<ul id="detail_menu">
	{block name='frontend_detail_actions_review'}
	{if !{config name=VoteDisable}}
		<li>
			<a href="#write_comment" class="write_comment" rel="nofollow" title="{s name='DetailLinkReview'}{/s}">
			{se name="DetailLinkReview"}{/se}
		</a>
		</li>
	{/if}
	{/block}
	
	{block name='frontend_detail_actions_contact'}
	<li>
		<a href="{$sInquiry}" rel="nofollow" title="{s name='DetailLinkContact'}{/s}">
			{se name="DetailLinkContact"}{/se}
		</a>
	</li>
    <li>
        <a href="{url controller='compare' action='add_article' articleID=$sArticle.articleID}" rel="nofollow" title="{s name='DetailActionLinkCompare'}Artikel vergleichen{/s}" class="compare_add_article hide_script">
            {se name="DetailActionLinkCompare"}{/se}
        </a>
    </li>
	{/block}
	
	{block name='frontend_detail_actions_notepad'}
	<li>
		<a href="{url controller='note' action='add' ordernumber=$sArticle.ordernumber}" rel="nofollow" title="{s name='DetailLinkNotepad'}{/s}">
			{se name="DetailLinkNotepad"}{/se}
		</a>
	</li>
	{/block}
	
	{block name='frontend_detail_actions_voucher'}
		{if {config name=showTellAFriend}}
			<li class="lastrow">
				<a href="{$sArticle.linkTellAFriend}" rel="nofollow" title="{s name='DetailLinkVoucher'}{/s}">
					{se name="DetailLinkVoucher"}{/se}
				</a>
			</li>
		{/if}
	{/block}
</ul>
