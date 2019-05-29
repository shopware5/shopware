{extends file='frontend/index/header.tpl'}

{block name="frontend_index_header_meta_keywords"}{/block}

{block name='frontend_index_header_meta_robots'}{s name='IndexMetaRobots'}index,follow{/s}{/block}

{block name='frontend_index_header_meta_tags_opengraph'}
    {s name="IndexMetaDescriptionStandard" namespace="frontend/index/header" assign="description"}{/s}
    {s name="IndexMetaDescription" assign="contentTypeDescription"}{/s}
    {s name="IndexMetaImage" assign="contentTypeImage"}{/s}
    {s name="IndexMetaTitle" assign="contentTypeTitle"}{/s}

    {if $contentTypeDescription}
        {$description = $contentTypeDescription}
    {/if}

    {if !$contentTypeTitle}
        {$contentTypeTitle = $sType->getName()}
    {/if}

    {$description = $description|truncate:$SeoDescriptionMaxLength:'…'}

    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{config name=sShopname}|escapeHtml}" />
    <meta property="og:title" content="{$contentTypeTitle|escapeHtml}" />
    <meta property="og:description" content="{$description|escapeHtml}" />

    <meta name="twitter:card" content="website" />
    <meta name="twitter:site" content="{{config name=sShopname}|escapeHtml}" />
    <meta name="twitter:title" content="{$contentTypeTitle|escapeHtml}" />
    <meta name="twitter:description" content="{$description|escapeHtml}" />

    {* Images *}
    {if !$contentTypeImage}
        {foreach $sItems as $sItem}
            {if $sItem@first}
                {$image = $sItem[$sImageKey]}
                {if $image[0]}
                    {* Image-Slider, take first image *}
                    {$image = $image[0]}
                {/if}

                {$contentTypeImage = $image.source}
                {break}
            {/if}
        {/foreach}
    {/if}

    <meta property="og:image" content="{$contentTypeImage}" />
    <meta name="twitter:image" content="{$contentTypeImage}" />
{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{strip}
    {s name="IndexMetaDescriptionStandard" namespace="frontend/index/header" assign="description"}{/s}
    {s name="IndexMetaDescription" assign="contentTypeDescription"}{/s}

    {if $contentTypeDescription}
        {$description = $contentTypeDescription}
    {/if}

    {$description = $description|truncate:$SeoDescriptionMaxLength:'…'}
    {$description}
{/strip}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    <link rel="canonical" href="{url controller=$Controller action=index}"/>
{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}{/block}
