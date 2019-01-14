{* Banner *}
{block name="frontend_listing_index_banner"}
    {if !$hasEmotion && !$isHomePage}
        {include file='frontend/listing/banner.tpl'}
    {/if}
{/block}

{* Category headline *}
{block name="frontend_listing_index_text"}
    {if !$hasEmotion && !$isHomePage}
        {include file='frontend/listing/text.tpl'}
    {/if}
{/block}

{* Topseller *}
{block name="frontend_listing_index_topseller"}
    {if !$hasEmotion && {config name=topSellerActive}  && !$isHomePage}
        {action module=widgets controller=listing action=top_seller sCategory=$sCategoryContent.id}
    {/if}
{/block}

{* Listing *}
{block name="frontend_listing_index_listing"}
    {* Emotion worlds *}
    {block name="frontend_listing_list_promotion"}
        {$emotionViewports = [0 => 'xl', 1 => 'l', 2 => 'm', 3 => 's', 4 => 'xs']}

        {if $hasEmotion}
            {if $isHomePage}
                {$Controller = 'index'}
            {/if}

            {$fullscreen = false}

            {block name="frontend_listing_emotions"}
                <div class="content--emotions">

                    {foreach $emotions as $emotion}
                        {if $emotion.fullscreen == 1}
                            {$fullscreen = true}
                        {/if}

                        {block name="frontend_listing_emotions_emotion"}
                            {include file="frontend/_includes/emotion.tpl"}
                        {/block}
                    {/foreach}

                    {if !$isHomePage}
                        {block name="frontend_listing_list_promotion_link_show_listing"}

                            {$showListingCls = "emotion--show-listing"}

                            {foreach $showListingDevices as $device}
                                {$showListingCls = "{$showListingCls} hidden--{$emotionViewports[$device]}"}
                            {/foreach}

                            {if $showListingButton}
                                <div class="{$showListingCls}{if $fullscreen} is--align-center{/if}">
                                    <a href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}" title="{$sCategoryContent.name|escape}" class="link--show-listing{if $fullscreen} btn is--primary{/if}">
                                        {s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie &raquo;{/s}
                                    </a>
                                </div>
                            {/if}
                        {/block}
                    {/if}
                </div>
            {/block}
        {/if}
    {/block}

    {* Listing wrapper *}
    {block name="frontend_listing_listing_wrapper"}
        {if $showListing}
            {$listingCssClass = "listing--wrapper"}

            {foreach $showListingDevices as $device}
                {$listingCssClass = "{$listingCssClass} visible--{$emotionViewports[$device]}"}
            {/foreach}

            {if $theme.sidebarFilter}
                {$listingCssClass = "{$listingCssClass} has--sidebar-filter"}
            {/if}

            <div class="{$listingCssClass}">
                {action module=frontend controller=listing action=listing params=$params}
            </div>
        {/if}
    {/block}
{/block}

{* Tagcloud *}
{block name="frontend_listing_index_tagcloud"}
    {if {config name=show namespace=TagCloud } && !$isHomePage}
        {action module=widgets controller=listing action=tag_cloud sController=listing sCategory=$sCategoryContent.id}
    {/if}
{/block}
