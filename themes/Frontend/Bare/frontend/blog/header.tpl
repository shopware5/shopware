{extends file='frontend/index/header.tpl'}

{* Title *}
{block name='frontend_index_header_title'}{if $sArticle.metaTitle}{$sArticle.metaTitle} | {config name=sShopname}{else}{$smarty.block.parent}{/if}{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sArticle.metaKeyWords}{$sArticle.metaKeyWords}{else}{if $sCategoryContent.metaKeywords}{$sCategoryContent.metaKeywords}{/if}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sArticle.metaDescription}{$sArticle.metaDescription|strip_tags|escape}{else}{if $sCategoryContent.metaDescription}{$sCategoryContent.metaDescription|strip_tags|escape}{/if}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    {* Count of available product pages *}
    {$pages = ceil($sNumberArticles / $sPerPage)}
    
    {if {config name=seoIndexPaginationLinks} && $pages > 1}
        
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
    {elseif $pages > 1}
        <link rel="canonical"
              href="{if $sCategoryContent.canonicalParams}{url params = $sCategoryContent.canonicalParams}{elseif $sCategoryContent.sSelfCanonical}{$sCategoryContent.sSelfCanonical}{else}{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}{/if}"
              title="{if $sCategoryContent.description}{$sCategoryContent.description|escape}{else}{$sShopname|escape}{/if}"/>
    {/if}
{/block}


{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}
<link rel="alternate" type="application/rss+xml" title="{$sCategoryContent.description|escape} RSS"
      href="{$sCategoryContent.rssFeed}"/>
<link rel="alternate" type="application/atom+xml" title="{$sCategoryContent.description|escape} ATOM"
      href="{$sCategoryContent.atomFeed}"/>
{/block}
