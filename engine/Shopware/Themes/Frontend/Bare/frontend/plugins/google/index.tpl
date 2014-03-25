{block name='frontend_index_header_javascript' append}
{if $GoogleTrackingID}
{include file="frontend/plugins/google/analytics.tpl"}
{/if}
{/block}
{block name='frontend_checkout_finishs_transaction_number' append}
{if $GoogleConversionID}
{include file="frontend/plugins/google/adwords.tpl"}
{/if}
{/block}