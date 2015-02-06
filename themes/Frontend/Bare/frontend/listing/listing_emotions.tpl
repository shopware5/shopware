{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_emotions"}
    <div class="content--emotions">
        {foreach $emotions as $emotion}

            {if $emotion.showListing == 1}
                {$showListing = true}
            {/if}

            <div class="emotion--wrapper"
                 data-controllerUrl="{url module=widgets controller=emotion action=index emotionId=$emotion.id controllerName=$Controller}"
                 data-availableDevices="{$emotion.devices}"
                 data-showListing="{if $emotion.showListing == 1}true{else}false{/if}">
            </div>
        {/foreach}

        {block name="frontend_listing_list_promotion_link_show_listing"}
            {if !$showListing}
                <div class="emotion--show-listing">
                    <a href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}" class="link--show-listing">
                        {s name="ListingActionsOffersLink"}{/s}
                    </a>
                </div>
            {/if}
        {/block}
    </div>
{/block}