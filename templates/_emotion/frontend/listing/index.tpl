{extends file='parent:frontend/listing/index.tpl'}

{* Sidebar right *}
{block name='frontend_index_content_right'}{/block}

{* Tagcloud *}
{block name="frontend_listing_index_tagcloud"}
{if {config name=show namespace=TagCloud }}
    {action module=widgets controller=listing action=tag_cloud sCategory=$sCategoryContent.id}
{/if}
{/block}

{block name="frontend_listing_index_listing"}
    {include file='frontend/listing/listing.tpl' sTemplate=$sTemplate}
    {if $sCategoryContent.parent != 1 && ! $showListing && !$sSupplierInfo}
        <div class="emotion-link">
            <a class="emotion-offers" href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}">
                {s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie{/s}
            </a>
        </div>
        <div class="space">&nbsp;</div>
    {/if}
{/block}

{* Topseller slider *}
{block name="frontend_listing_index_banner"}
    {if !$sLiveShopping}
        {include file='frontend/listing/banner.tpl' sLiveShopping=$sLiveShopping}
    {/if}
{/block}

{block name="frontend_listing_index_text" append}
	{if !$hasEmotion && !$sSupplierInfo && {config name=topSellerActive}}
	    {action module=widgets controller=listing action=top_seller sCategory=$sCategoryContent.id}
	{/if}
{/block}

{* Trusted shops logo *}
{block name='frontend_index_left_trustedshops'}
    {block name="frontend_listing_left_additional_features"}
        {include file="frontend/listing/right.tpl"}
        <div class="clear">&nbsp;</div>
    {/block}

    {if {config name=TSID}}
        {include file='frontend/plugins/trusted_shops/logo.tpl'}
    {/if}
{/block}


{* Hide listing if we're having a emotion here *}
{block name="frontend_listing_list_inline"}
    {if $showListing}
        {$smarty.block.parent}
    {/if}
{/block}

{* Listing actions top *}
{block name="frontend_listing_top_actions"}
    {if $showListing}
        {$smarty.block.parent}
    {/if}
{/block}

{* Listing actions bottom *}
{block name="frontend_listing_bottom_paging"}
    {if $showListing}
        {$smarty.block.parent}
    {/if}
{/block}

{* Category text *}
{block name="frontend_listing_index_text"}
    {if !$hasEmotion && !$sSupplierInfo}
        {$smarty.block.parent}
    {/if}
{/block}
