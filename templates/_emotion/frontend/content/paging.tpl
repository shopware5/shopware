{extends file="frontend/listing/listing_actions.tpl"}

{block name='frontend_listing_actions_top'}{/block}
{block name="frontend_listing_actions_change_layout"}
{/block}
{block name='frontend_listing_actions_paging'}
{if $sPages.numbers > 1}
<div class="top">
	{* Paging *}
	<div class="paging">
		<label>{s name='ListingPaging'}{/s}</label>
		{if $sPages.previous}
			<a href="{$sPages.previous|rewrite:$sCategoryInfo.name}" title="{s name='ListingLinkPrevious'}{/s}" class="navi prev">{s name="ListingTextPrevious"}&lt;{/s}</a>
		{/if}
		
		{* Articles per page *}
		{foreach from=$sPages.numbers item=page}
			{if $page.value<$sPage+4 AND $page.value>$sPage-4}
				{if $page.markup AND (!$sOffers OR $sPage)}
					<a title="{$sCategoryInfo.name}" class="navi on">{$page.value}</a>
				{else}
					<a href="{$page.link|rewrite:$sCategoryInfo.name}" class="navi">{$page.value}</a>
				{/if}
			{elseif $page.value==$sPage+4 OR $page.value==$sPage-4}
				<div class="more">...</div>
			{/if}
		{/foreach}
		{if $sPages.next}
			<a href="{$sPages.next|rewrite:$sCategoryInfo.name}" title="{s name='ListingLinkNext'}{/s}" class="navi more">{s name="ListingTextNext"}&gt;{/s}</a>
		{/if}
	</div>
</div>
{/if}
{/block}