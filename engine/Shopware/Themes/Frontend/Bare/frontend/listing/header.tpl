{extends file='frontend/index/header.tpl'}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sCategoryContent.metakeywords}{$sCategoryContent.metakeywords}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sCategoryContent.metadescription}{$sCategoryContent.metadescription|strip_tags|escape}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
<link rel="canonical" href="{$sCategoryContent.sSelfCanonical}" title="{if $sCategoryContent.canonicalTitle}{$sCategoryContent.canonicalTitle|escape}{elseif $sCategoryContent.description}{$sCategoryContent.description|escape}{else}{$sShopname|escape}{/if}" />

{* Count of available product pages *}
{$pages = ceil($sNumberArticles / $criteria->getLimit())}

{if $pages >= $sPage + 1}
    {$pageNext = $sPage + 1}
{/if}

{if $sPage >= 1}
    {$pagePrevious = $sPage - 1}
{/if}

{* Previous rel tag for infinite scrolling *}
{if $theme.infiniteScrolling && $pagePrevious}
<link rel="prev" href="{$pagePrevious|rewrite:$sCategoryInfo.name}">
{/if}

{* Next rel tag for infinite scrolling *}
{if $theme.infiniteScrolling && $pageNext}
<link rel="next" href="{$pageNext|rewrite:$sCategoryInfo.name}">
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