{block name="frontend_detail_index_notification" prepend}
    {if $liveShopping}
        {*include specify live shopping template for the different types*}
        {if $liveShopping.type === 1}
            {include file="frontend/plugins/live_shopping/live_shopping_offer.tpl" liveShopping=$liveShopping}
        {else}
            {include file="frontend/plugins/live_shopping/live_shopping_time.tpl" liveShopping=$liveShopping}
        {/if}
    {/if}
{/block}

{block name="frontend_detail_data_liveshopping"}
{/block}