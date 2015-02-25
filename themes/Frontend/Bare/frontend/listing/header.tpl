{extends file='frontend/index/header.tpl'}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sCategoryContent.metaKeywords}{$sCategoryContent.metaKeywords}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sCategoryContent.metadescription}{$sCategoryContent.metadescription|strip_tags|escape}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    {* Count of available product pages *}
    {$pages = ceil($sNumberArticles / $criteria->getLimit())}
    
    {if {config name=seoIndexPaginationLinks} && $showListing && $pages > 1}
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
    {elseif !{config name=seoIndexPaginationLinks} || !$showListing}
        <link rel="canonical" href="{url params = $sCategoryContent.canonicalParams}" />
    {/if}
{/block}

{* Title *}
{block name='frontend_index_header_title'}{strip}
    {if $sCategoryContent.title}{$sCategoryContent.title}{else}{$smarty.block.parent}{/if}
{/strip}{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}
<link rel="alternate" type="application/rss+xml" title="{$sCategoryContent.description|escape}" href="{$sCategoryContent.rssFeed}" />
<link rel="alternate" type="application/atom+xml" title="{$sCategoryContent.description|escape}" href="{$sCategoryContent.atomFeed}" />
{/block}

{* Google optimized crawling *}
{block name='frontend_index_header_meta_tags' append}
    {if $hasEmotion && !$hasEscapedFragment}
        <meta name="fragment" content="!">
    {/if}
{/block}