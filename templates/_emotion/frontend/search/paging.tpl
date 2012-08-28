{extends file='parent:frontend/search/paging.tpl'}

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