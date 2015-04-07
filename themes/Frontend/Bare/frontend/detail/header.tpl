{extends file='frontend/index/header.tpl'}

{* Meta title *}
{block name="frontend_index_header_title"}{if $sArticle.metaTitle}{$sArticle.metaTitle} | {config name=sShopname}{else}{$sArticle.articleName} | {$smarty.block.parent}{/if}{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sArticle.keywords}{$sArticle.keywords}{elseif $sArticle.sDescriptionKeywords}{$sArticle.sDescriptionKeywords}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sArticle.description}{$sArticle.description|escape}{else}{$sArticle.description_long|strip_tags|escape}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    <link rel="canonical" href="{url sArticle=$sArticle.articleID title=$sArticle.articleName}" />
{/block}