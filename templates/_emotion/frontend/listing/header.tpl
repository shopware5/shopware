
{extends file='frontend/index/header.tpl'}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sCategoryContent.metakeywords}{$sCategoryContent.metakeywords}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sCategoryContent.metadescription}{$sCategoryContent.metadescription|strip_tags|escape}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
<link rel="canonical" href="{$sCategoryContent.sSelfCanonical}" title="{if $sCategoryContent.canonicalTitle}{$sCategoryContent.canonicalTitle}{elseif $sCategoryContent.description}{$sCategoryContent.description}{else}{$sShopname}{/if}" />
{/block}

{* Title *}
{block name='frontend_index_header_title'}{strip}
    {if $sCategoryContent.title}{$sCategoryContent.title}{else}{$smarty.block.parent}{/if}
{/strip}{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}
<link rel="alternate" type="application/rss+xml" title="{$sCategoryContent.description}" href="{$sCategoryContent.rssFeed}" />
<link rel="alternate" type="application/atom+xml" title="{$sCategoryContent.description}" href="{$sCategoryContent.atomFeed}" />
{/block}
