{* Sorting and changing layout *}
{block name="frontend_listing_top_actions"}
    {if $showListing && !$sOffers}
        {include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
    {/if}
{/block}

{* Hide actual listing if a emotion world is active *}
{if !$sOffers}
    {block name="frontend_listing_listing_outer"}
        {include file="frontend/listing/listing_outer.tpl"}
    {/block}
{else}
    {if $sCategoryContent.parent != 1}
		<a href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}">
			{s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie &raquo;{/s}
		</a>
    {/if}
{/if}

{* Paging *}
{block name="frontend_listing_bottom_paging"}
	{if $showListing && $pages > 1}
		<div class="listing--bottom-paging">
			{include file="frontend/listing/actions/action-pagination.tpl"}
		</div>
	{/if}
{/block}