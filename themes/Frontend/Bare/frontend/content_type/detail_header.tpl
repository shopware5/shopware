{extends file='frontend/index/header.tpl'}

{block name="frontend_index_header_meta_keywords"}{/block}
{block name='frontend_index_header_meta_robots'}{s name='DetailMetaRobots'}index,follow{/s}{/block}

{block name='frontend_index_header_meta_tags_opengraph'}
    {s name="IndexMetaDescriptionStandard" namespace="frontend/index/header" assign="description"}{/s}
    {s name="IndexMetaDescription" assign="contentTypeDescription"}{/s}
    {s name="IndexMetaImage" assign="contentTypeImage"}{/s}
    {s name="IndexMetaTitle" assign="contentTypeTitle"}{/s}

    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{config name=sShopname}|escapeHtml}" />
    <meta property="og:title" content="{$sItem[$sTitleKey]|escapeHtml}" />
    <meta property="og:description" content="{$sItem[$sDescriptionKey]|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}" />

    <meta name="twitter:card" content="website" />
    <meta name="twitter:site" content="{{config name=sShopname}|escapeHtml}" />
    <meta name="twitter:title" content="{$sItem[$sTitleKey]|escapeHtml}" />
    <meta name="twitter:description" content="{$sItem[$sDescriptionKey]|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}" />

    {$image = $sItem[$sImageKey]}
    {if $image[0]}
        {* Image-Slider, take first image *}
        {$image = $image[0]}
    {/if}

    <meta property="og:image" content="{$image.source}" />
    <meta name="twitter:image" content="{$image.source}" />
{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{strip}
    {s name="IndexMetaDescriptionStandard" namespace="frontend/index/header" assign="description"}{/s}
    {s name="IndexMetaDescription" assign="contentTypeDescription"}{/s}

    {$sItem[$sDescriptionKey]|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'}
{/strip}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    <link rel="canonical" href="{url controller=$Controller action=detail id=$sItem.id}"/>
{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}{/block}
