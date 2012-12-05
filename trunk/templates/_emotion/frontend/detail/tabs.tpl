{extends file='parent:frontend/detail/tabs.tpl'}

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