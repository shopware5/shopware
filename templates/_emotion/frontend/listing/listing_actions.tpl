{if !$sOffers}

{block name="frontend_listing_actions_class"}
<div class="listing_actions{if !$sPages || $sPages.numbers|@count <= 1 || $sNumberPages <= 1} normal{/if}">
{/block}
{block name='frontend_listing_actions_top'}
	<div class="top">

		{* Sort filter *}
		{block name='frontend_listing_actions_sort'}
			<form method="get" action="{url controller=cat sCategory=$sCategoryContent.id}">
            {foreach from=$categoryParams key=key item=value}
                {if $key == 'sSort' || $key == $shortParameters.sSort}
                    {continue}
                {/if}
                <input type="hidden" name="{$key}" value="{$value}">
            {/foreach}
            <input type="hidden" name="{$shortParameters.sPage}" value="1">
			<div class="sort-filter">
				<label>{s name='ListingLabelSort'}{/s}</label>
				<select name="{$shortParameters.sSort}" class="auto_submit">
					<option value="1"{if $sSort eq 1} selected="selected"{/if}>{s name='ListingSortRelease'}{/s}</option>
					<option value="2"{if $sSort eq 2} selected="selected"{/if}>{s name='ListingSortRating'}{/s}</option>
					<option value="3"{if $sSort eq 3} selected="selected"{/if}>{s name='ListingSortPriceLowest'}{/s}</option>
					<option value="4"{if $sSort eq 4} selected="selected"{/if}>{s name='ListingSortPriceHighest'}{/s}</option>
					<option value="5"{if $sSort eq 5} selected="selected"{/if}>{s name='ListingSortName'}{/s}</option>
					{block name='frontend_listing_actions_sort_values'}{/block}
				</select>
			</div>
			</form>
		{/block}

		{* Article per page *}
		{block name='frontend_listing_actions_items_per_page'}
		{if $sPerPage}
			<form method="get" action="{url controller=cat sCategory=$sCategoryContent.id}">
            {foreach from=$categoryParams key=key item=value}
                {if $key == 'sPerPage' || $key == $shortParameters.sPerPage}
                    {continue}
                {/if}
                <input type="hidden" name="{$key}" value="{$value}">
            {/foreach}
            <input type="hidden" name="{$shortParameters.sPage}" value="1">
            <div class="articleperpage{if $sCategoryContent.noViewSelect} rightalign{/if}">
				<label>{s name='ListingLabelItemsPerPage'}{/s}</label>
				<select name="{$shortParameters.sPerPage}" class="auto_submit">
				{foreach from=$sPerPage item=perPage}
			        <option value="{$perPage.value}" {if $perPage.markup}selected="selected"{/if}>
                        {$perPage.value}
                    </option>
				{/foreach}
				</select>
			</div>
			</form>
		{/if}
		{/block}

		{* Change layout *}

		{block name="frontend_listing_actions_change_layout"}
		{if !$sCategoryContent.noViewSelect}
            {assign var="templateLinks" value=array()}
            {foreach from=$categoryParams key=key item=value}
                {if $key == 'sTemplate' || $key == $shortParameters.sTemplate}
                    {continue}
                {/if}
                {$templateLinks[$key] = $value}
            {/foreach}

			<div class="list-settings">
                <label>{s name='ListingActionsSettingsTitle'}Darstellung:{/s}</label>
                <a href="{url params=$templateLinks sViewport='cat' sCategory=$sCategoryContent.id sPage=1 sTemplate='table'}" class="table-view {if $sBoxMode=='table'}active{/if}" title="{s name='ListingActionsSettingsTable'}Tabellen-Ansicht{/s}">&nbsp;</a>
                <a href="{url params=$templateLinks sViewport='cat' sCategory=$sCategoryContent.id sPage=1 sTemplate='list'}" class="list-view {if $sBoxMode=='list'}active{/if}" title="{s name='ListingActionsSettingsList'}Listen-Ansicht{/s}">&nbsp;</a>
            </div>
		{/if}
		{/block}

		<noscript>
			<input type="submit" class="buttonkit green small rounded" value="OK" />
		</noscript>
	</div>
{/block}
{block name='frontend_listing_actions_paging'}
	{if $sNumberPages && $sNumberPages > 1}
	<div class="bottom">

		{* Paging *}
		<div class="paging">
			<label>{s name='ListingPaging'}{/s}</label>
			{if $sPages.previous}
				<a href="{$sPages.previous|rewrite:$sCategoryContent.name}" title="{s name='ListingLinkPrevious'}{/s}" class="navi prev">{s name="ListingTextPrevious"}&lt;{/s}</a>
			{/if}

			{* Articles per page *}
			{foreach from=$sPages.numbers item=page}
				{if $page.value<$sPage+4 AND $page.value>$sPage-4}
					{if $page.markup AND (!$sOffers OR $sPage)}
						<a title="{$sCategoryContent.name}" class="navi on">{$page.value}</a>
					{else}
						<a href="{$page.link|rewrite:$sCategoryContent.name}" class="navi">{$page.value}</a>
					{/if}
				{elseif $page.value==$sPage+4 OR $page.value==$sPage-4}
					<div class="more">...</div>
				{/if}
			{/foreach}
			{if $sPages.next}
				<a href="{$sPages.next|rewrite:$sCategoryContent.name}" title="{s name='ListingLinkNext'}{/s}" class="navi more">{s name="ListingTextNext"}&gt;{/s}</a>
			{/if}
		</div>

		{block name='frontend_listing_actions_count'}
		{* Count sites *}
		<div class="display_sites">
			{se name="ListingTextSite"}Seite{/se} <strong>{$sPage}</strong> {se name="ListingTextFrom"}von{/se} <strong>{$sNumberPages}</strong>
		</div>
		{/block}
	</div>
	{/if}
{/block}
{block name="frontend_listing_actions_close"}
</div>
<div class="space">&nbsp;</div>
{/block}
{else}
	{if $sCategoryContent.parent != 1}
	<div class="listing_actions normal">
		<div class="top">
			<a class="offers" href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}">
				{s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie &raquo;{/s}
			</a>
		</div>
	</div>
	<div class="space">&nbsp;</div>
	{/if}
{/if}
