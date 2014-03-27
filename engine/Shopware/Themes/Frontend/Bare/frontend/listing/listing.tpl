{* Vendor filter *}
{block name="frontend_listing_list_filter_supplier"}
    {include file="frontend/listing/vendor-info.tpl"}
{/block}

{* Sorting and changing layout *}
{block name="frontend_listing_top_actions"}
    {if $showListing && !$sOffers}
        {include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
    {/if}
{/block}

{* Hide actual listing if a emotion world is active *}
{if !$sOffers}
    {block name="frontend_listing_listing_outer"}
        <ul class="listing listing--{$sTemplate}">
            {block name="frontend_listing_list_inline"}

                {* Actual listing *}
                {if $showListing}
                    {foreach $sArticles as $sArticle}
                        {include file="frontend/listing/box_article.tpl" sTemplate=$sTemplate lastitem=$sArticle@last firstitem=$sArticle@first}
                    {/foreach}
                {/if}
            {/block}
        </ul>
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

{* Paging *}
{block name="frontend_listing_bottom_paging"}
	{if $showListing}
		{if !$sOffers}
		    {include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
		{else}
			{if $sCategoryContent.parent != 1}
			<div class="actions_offer">
				{include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
			</div>
			{/if}
		{/if}
	{/if}
{/block}