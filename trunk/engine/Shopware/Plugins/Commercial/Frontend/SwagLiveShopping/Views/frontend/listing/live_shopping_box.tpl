{* disabled actions *}
{block name='frontend_listing_box_article_actions'}
    {if $sArticle.liveShopping}
        {$liveShopping = $sArticle.liveShopping}

        {if $emotion}
            <div class="liveshopping_wrapper col{$colWidth}">
                {if $colWidth eq $emotion.cols || $colWidth gte 3}
                    <div class="detail_wrapper">
                        {if $liveShopping.type eq 1}
                            {include file="frontend/plugins/live_shopping/live_shopping_offer.tpl" liveShopping=$liveShopping inListing=true}
                        {else}
                            {include file="frontend/plugins/live_shopping/live_shopping_time.tpl" liveShopping=$liveShopping inListing=true}
                        {/if}
                    </div>
                {else}
                    {if $liveShopping.type eq 1}
                        {include file="frontend/listing/live_shopping_offer.tpl" liveShopping=$liveShopping colWidth=$colWidth}
                    {else}
                        {include file="frontend/listing/live_shopping_time.tpl" liveShopping=$liveShopping colWidth=$colWidth}
                    {/if}
                {/if}
            </div>
        {else}
            <div class="liveshopping_wrapper {$sTemplate}">
                {if $sTemplate === 'listing-1col'}
                    <div class="detail_wrapper">
                        {if $liveShopping.type eq 1}
                            {include file="frontend/plugins/live_shopping/live_shopping_offer.tpl" liveShopping=$liveShopping inListing=true}
                        {else}
                            {include file="frontend/plugins/live_shopping/live_shopping_time.tpl" liveShopping=$liveShopping inListing=true}
                        {/if}
                    </div>
                {else}
                    {if $liveShopping.type eq 1}
                        {include file="frontend/listing/live_shopping_offer.tpl" liveShopping=$liveShopping inListing=true}
                    {else}
                        {include file="frontend/listing/live_shopping_time.tpl" liveShopping=$liveShopping inListing=true}
                    {/if}
                {/if}
            </div>
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* disabled actions *}
{block name='frontend_listing_box_article_price'}
    {if $sArticle.liveShopping}

    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* disabled actions *}
{block name='frontend_listing_box_article_description'}
    {if $sArticle.liveShopping}

    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* New *}
{block name='frontend_listing_box_article_new'}
    {if $sArticle.liveShopping}

    {else}
        {$smarty.block.parent}
    {/if}
{/block}
