{if $productBoxLayout == 'minimal'}
    {include file="frontend/listing/product-box/box-minimal.tpl"}
{elseif $productBoxLayout == 'image'}
    {include file="frontend/listing/product-box/box-big-image.tpl"}
{else}
    {include file="frontend/listing/product-box/box-basic.tpl" productBoxLayout="basic"}
{/if}