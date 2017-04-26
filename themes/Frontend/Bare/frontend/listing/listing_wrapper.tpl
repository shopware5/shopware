{* Emotion worlds *}
{block name="frontend_listing_list_promotion"}
    {$emotionViewports = [0 => 'xl', 1 => 'l', 2 => 'm', 3 => 's', 4 => 'xs']}

    {if $hasEmotion}
        {$fullscreen = false}

        {block name="frontend_listing_emotions"}
            <div class="content--emotions">

                {foreach $emotions as $emotion}
                    {if $emotion.fullscreen == 1}
                        {$fullscreen = true}
                    {/if}

                    <div class="emotion--wrapper"
                         data-controllerUrl="{url module=widgets controller=emotion action=index emotionId=$emotion.id controllerName=$Controller}"
                         data-availableDevices="{$emotion.devices}">
                    </div>
                {/foreach}

                {block name="frontend_listing_list_promotion_link_show_listing"}

                    {$showListingCls = "emotion--show-listing"}

                    {foreach $showListingDevices as $device}
                        {$showListingCls = "{$showListingCls} hidden--{$emotionViewports[$device]}"}
                    {/foreach}

                    <div class="{$showListingCls}{if $fullscreen} is--align-center{/if}">
                        <a href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}" title="{$sCategoryContent.name|escape}" class="link--show-listing{if $fullscreen} btn is--primary{/if}">
                            {s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie &raquo;{/s}
                        </a>
                    </div>
                {/block}
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
            {action module=frontend controller=listing action=listing params=$ajaxCountUrlParams}
        </div>
    {/if}
{/block}