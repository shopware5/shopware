{block name="frontend_listing_box_article_includes"}
    {$path = ''}
    {if $productBoxLayout == 'minimal'}
        {$path = "frontend/listing/product-box/box-minimal.tpl"}

    {elseif $productBoxLayout == 'image'}
        {$path = "frontend/listing/product-box/box-big-image.tpl"}

    {elseif $productBoxLayout == 'slider'}
        {$path = "frontend/listing/product-box/box-product-slider.tpl"}

    {elseif $productBoxLayout == 'emotion'}
        {$path = "frontend/listing/product-box/box-emotion.tpl"}

    {elseif $productBoxLayout == 'list'}
        {$path = "frontend/listing/product-box/box-list.tpl"}

    {elseif $path == ''}
        {$path = "frontend/listing/product-box/box-$productBoxLayout.tpl"}
        {if !$path|template_exists}
            {$path = ''}
        {/if}
    {/if}
    
    {if $path == ''}
        {block name="frontend_listing_box_article_includes_additional"}
            {include file="frontend/listing/product-box/box-basic.tpl" productBoxLayout="basic"}
        {/block}
    {else}
        {include file=$path}
    {/if}
{/block}
