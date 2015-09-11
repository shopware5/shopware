
{extends file='frontend/index/header.tpl'}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sCategoryContent.metaKeywords}{$sCategoryContent.metaKeywords}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sCategoryContent.metaDescription}{$sCategoryContent.metaDescription|strip_tags|escape}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}

    {if {config name=seoIndexPaginationLinks} 
        && $showListing 
        && $criteria 
        && ceil($sNumberArticles / $criteria->getLimit()) > 1
    }
        {* Count of available product pages *}
        {$pages = ceil($sNumberArticles / $criteria->getLimit())}
        {include file="frontend/listing/header_seo_pagination.tpl"}
    {elseif !{config name=seoIndexPaginationLinks} || !$showListing}
        <link rel="canonical"
              href="{if $sCategoryContent.canonicalParams}{url params = $sCategoryContent.canonicalParams}{else}{$sCategoryContent.sSelfCanonical}{/if}"
              title="{if $sCategoryContent.canonicalTitle}{$sCategoryContent.canonicalTitle}{elseif $sCategoryContent.description}{$sCategoryContent.description}{else}{$sShopname}{/if}"
                />
    {/if}
{/block}

{* Title *}
{block name='frontend_index_header_title'}{strip}
    {if $sCategoryContent.metaTitle}
        {$sCategoryContent.metaTitle} | {config name=sShopname}
    {elseif $sCategoryContent.title}
        {$sCategoryContent.title}
    {else}
        {$smarty.block.parent}
    {/if}
{/strip}{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}
<link rel="alternate" type="application/rss+xml" title="{$sCategoryContent.description}" href="{$sCategoryContent.rssFeed}" />
<link rel="alternate" type="application/atom+xml" title="{$sCategoryContent.description}" href="{$sCategoryContent.atomFeed}" />
{/block}
