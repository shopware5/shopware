{extends file='frontend/listing/listing_actions.tpl'}

{block name="frontend_listing_actions_class"}
<div class="listing_actions{if !$sPages || $sPages.count <= 1} normal{/if}">
{/block}
{block name="frontend_listing_actions_change_layout"}
{/block}
{* Listing paging *}
{block name='frontend_listing_actions_paging'}
{if $sPages.pages|@count != 0}
	<div class="bottom">
		<div class="paging">
			<label>{s name='ListingPaging'}{/s}</label>
			{if isset($sPages.before)}
				<a href="{$sLinks.sPage}&sPage={$sPages.before}" title="{s name='ListingLinkNext'}{/s}" class="navi prev">
					{s name="ListingTextPrevious"}&lt;{/s}
				</a>
			{/if}

			{foreach from=$sPages.pages item=page}
                {if $page<$sRequests.sPage+4 AND $page>$sRequests.sPage-4}
                    {if $sRequests.sPage==$page}
                        <a title="{$sCategoryInfo.name}" class="navi on">{$page}</a>
					{else}
                        <a href="{$sLinks.sPage}&sPage={$page}" title="{$sCategoryInfo.name}" class="navi">
                            {$page}
                        </a>
                    {/if}
				{elseif $page==$sRequests.sPage+4 OR $page==$sRequests.sPage-4}
                    <div class="more">...</div>
                {/if}
			{/foreach}
			{if $sPages.next}
				<a href="{$sLinks.sPage}&sPage={$sPages.next}" title="{s name='ListingLinkPrevious'}{/s}" class="navi more">{s name="ListingTextNext"}&gt;{/s}</a>
			{/if}
		</div>
	</div>
{/if}
{/block}

{block name='frontend_listing_actions_sort'}
<form name="frmsort" method="post" action="{$sLinks.sSort}">
	<div class="sort-filter">
		<label for="sSort">{s name='ListingLabelSort'}{/s}</label>
		<select name="sSort" id="sSort" class="auto_submit">
			<option value="7"{if $sRequests.sSort eq 7} selected="selected"{/if}>{s name='ListingSortRelevance'}{/s}</option>
			<option value="1"{if $sRequests.sSort eq 1} selected="selected"{/if}>{s name='ListingSortRelease'}{/s}</option>
			<option value="2"{if $sRequests.sSort eq 2} selected="selected"{/if}>{s name='ListingSortRating'}{/s}</option>
			<option value="3"{if $sRequests.sSort eq 3} selected="selected"{/if}>{s name='ListingSortPriceLowest'}{/s}</option>
			<option value="4"{if $sRequests.sSort eq 4} selected="selected"{/if}>{s name='ListingSortPriceHighest'}{/s}</option>
			<option value="5"{if $sRequests.sSort eq 5} selected="selected"{/if}>{s name='ListingSortName'}{/s}</option>
		</select>
	</div>
</form>	
{/block}

{block name='frontend_listing_actions_items_per_page'}
{if $sPerPage}
	<form method="post" action="{$sLinks.sPerPage}">
	<div class="articleperpage right">
		<label>{s name='ListingLabelItemsPerPage'}Artikel pro Seite:{/s}</label>
		<select name="sPerPage" class="auto_submit">
		{foreach from=$sPerPage item=perPage}
			<option value="{$perPage}" {if $perPage eq $sRequests.sPerPage}selected="selected"{/if}>{$perPage}</option>
		{/foreach}
		</select>
	</div>
	</form>
{/if}
{/block}