{extends file='frontend/index/header.tpl'}

{block name="frontend_index_header_meta_http_tags"}
    {$smarty.block.parent}

    {$sMetaTitle = $sItem[$sMetaTitleKey]}
    {$sMetaDescription = $sItem[$sMetaDescriptionKey]}

    {if empty($sMetaTitle)}
        {$sMetaTitle = $sItem[$sTitleKey]}
    {/if}

    {if empty($sMetaDescription)}
        {$sMetaDescription = $sItem[$sDescriptionKey]}
    {/if}
{/block}

{block name="frontend_index_header_meta_keywords"}{/block}
{block name='frontend_index_header_meta_robots'}{$sType->getSeoRobots()}{/block}

{block name='frontend_index_header_meta_tags_opengraph'}
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{config name=sShopname}|escapeHtml}" />
    <meta property="og:title" content="{$sMetaTitle|escapeHtml}" />
    <meta property="og:description" content="{$sMetaDescription|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}" />

    <meta name="twitter:card" content="website" />
    <meta name="twitter:site" content="{{config name=sShopname}|escapeHtml}" />
    <meta name="twitter:title" content="{$sMetaTitle|escapeHtml}" />
    <meta name="twitter:description" content="{$sMetaDescription|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}" />

    {$image = $sItem[$sImageKey]}
    {if is_array($image[0])}
        {* Image-Slider, take first image *}
        {$image = $image[0]}
    {/if}

    <meta property="og:image" content="{$image.source}" />
    <meta name="twitter:image" content="{$image.source}" />
{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{strip}
    {$sMetaDescription|strip_tags|trim|truncate:$SeoDescriptionMaxLength:'…'}
{/strip}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    {if $SeoMetaRobots|strpos:'noindex' === false}
        <link rel="canonical" href="{url controller=$Controller action=detail id=$sItem.id}"/>
    {/if}
{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}{/block}
