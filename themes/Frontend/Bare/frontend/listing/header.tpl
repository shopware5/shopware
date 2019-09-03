{extends file='frontend/index/header.tpl'}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sCategoryContent.metaKeywords}{$sCategoryContent.metaKeywords|escapeHtml}{/if}{/block}

{block name='frontend_index_header_meta_tags_opengraph'}

    {s name="IndexMetaDescriptionStandard" assign="description"}{/s}
    {if $sCategoryContent.cmstext}
        {$description = "{$sCategoryContent.cmstext|trim|strip_tags|escapeHtml}"}
    {elseif $sCategoryContent.metaDescription}
        {$description = "{$sCategoryContent.metaDescription|trim|strip_tags|escapeHtml}"}
    {/if}

    {$description = $description|truncate:$SeoDescriptionMaxLength:'…'}

    <meta property="og:type" content="product.group" />
    <meta property="og:site_name" content="{{config name=sShopname}|escapeHtml}" />
    <meta property="og:title" content="{$sCategoryContent.name|escapeHtml}" />
    <meta property="og:description" content="{$description|escapeHtml}" />

    <meta name="twitter:card" content="product" />
    <meta name="twitter:site" content="{{config name=sShopname}|escapeHtml}" />
    <meta name="twitter:title" content="{$sCategoryContent.name|escapeHtml}" />
    <meta name="twitter:description" content="{$description|escapeHtml}" />

    {* Images *}
    {if $sCategoryContent.media.source}
        {$metaImage = {$sCategoryContent.media.source}}
    {else}
        {foreach $sArticles as $sArticle}
            {if $sArticle@first}
                {$metaImage = $sArticle.image.source}
                {break}
            {/if}
        {/foreach}
    {/if}

    <meta property="og:image" content="{$metaImage}" />
    <meta name="twitter:image" content="{$metaImage}" />
{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sCategoryContent.metaDescription}{$sCategoryContent.metaDescription|strip_tags|escape}{else}{s name="IndexMetaDescriptionStandard"}{/s}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    {* Count of available product pages *}
    {$pages = 1}

    {if $criteria}
        {$pages = ceil($sNumberArticles / $criteria->getLimit())}
    {/if}

    {if $SeoMetaRobots|strpos:'noindex' === false}
        <link rel="canonical" href="{url params = $sCategoryContent.canonicalParams}"/>
    {/if}

    {if {config name=seoIndexPaginationLinks}}
        {if empty($sPage)}
            {$sPage = 0}
        {/if}

        {* Previous rel tag *}
        {if $sPage > 1}
            {$sCategoryContent.canonicalParams.sPage = $sPage - 1}
            <link rel="prev" href="{url params = $sCategoryContent.canonicalParams}">
        {/if}

        {* Next rel tag *}
        {if $pages >= $sPage + 1}
            {$sCategoryContent.canonicalParams.sPage = $sPage + 1}
            <link rel="next" href="{url params = $sCategoryContent.canonicalParams}">
        {/if}
    {/if}
{/block}

{* Title *}
{block name='frontend_index_header_title'}{strip}
    {if $sCategoryContent.metaTitle}
        {$sCategoryContent.metaTitle|escapeHtml} | {{config name=sShopname}|escapeHtml}
    {elseif $sCategoryContent.title}
        {$sCategoryContent.title|escapeHtml} | {{config name=sShopname}|escapeHtml}
    {else}
        {$smarty.block.parent}
    {/if}
{/strip}{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}
{/block}
