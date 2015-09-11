
{extends file='frontend/index/header.tpl'}

{* Title *}
{block name='frontend_index_header_title'}{if $sArticle.metaTitle}{$sArticle.metaTitle} | {config name=sShopname}{else}{$smarty.block.parent}{/if}{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sArticle.metaKeyWords}{$sArticle.metaKeyWords}{else}{if $sCategoryContent.metaKeywords}{$sCategoryContent.metaKeywords}{/if}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sArticle.metaDescription}{$sArticle.metaDescription|strip_tags|escape}{else}{if $sCategoryContent.metaDescription}{$sCategoryContent.metaDescription|strip_tags|escape}{/if}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    {* Count of available blog pages *}
    {$pages = ceil($sNumberArticles / $sPerPage)}

    {if {config name=seoIndexPaginationLinks} && $pages > 1}
        {include file="frontend/listing/header_seo_pagination.tpl"}
    {elseif $pages > 1}
        <link rel="canonical"
              href="{if $sCategoryContent.canonicalParams}{url params = $sCategoryContent.canonicalParams}{elseif $sCategoryContent.sSelfCanonical}{$sCategoryContent.sSelfCanonical}{else}{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}{/if}"
              title="{if $sCategoryContent.description}{$sCategoryContent.description}{else}{$sShopname}{/if}"/>
    {/if}
{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}
<link rel="alternate" type="application/rss+xml" title="{$sCategoryContent.description} RSS"
      href="{$sCategoryContent.rssFeed}"/>
<link rel="alternate" type="application/atom+xml" title="{$sCategoryContent.description} ATOM"
      href="{$sCategoryContent.atomFeed}"/>
{/block}
