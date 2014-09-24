{extends file="frontend/checkout/finish.tpl"}

{block name='frontend_index_header_javascript' append}
    {if $GoogleTrackingID}
        {if $GoogleTrackingLibrary == 'ga'}
            {include file="frontend/plugins/google/analytics.tpl"}
        {else}
            {include file="frontend/plugins/google/ua.tpl"}
        {/if}
    {/if}
{/block}

{block name='frontend_index_body_inline' append}
    {if $sOrderNumber || $sTransactionumber}
        {include file="frontend/plugins/google/adwords.tpl"}
    {/if}
{/block}