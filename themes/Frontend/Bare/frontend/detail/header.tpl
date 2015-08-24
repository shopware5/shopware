{extends file='frontend/index/header.tpl'}

{* Meta title *}
{block name="frontend_index_header_title"}{if $sArticle.metaTitle}{$sArticle.metaTitle} | {config name=sShopname}{else}{$sArticle.articleName} | {$smarty.block.parent}{/if}{/block}

{* Meta opengraph tags *}
{block name='frontend_index_header_meta_tags_opengraph'}
    <meta property="og:type" content="product" />
    <meta property="og:site_name" content="{config name=sShopname}" />
    <meta property="og:url" content="{url sArticle=$sArticle.articleID title=$sArticle.articleName}" />
    <meta property="og:title" content="{$sArticle.articleName|escape:'htmlall'}" />
    <meta property="og:description" content="{$sArticle.description_long|strip_tags|truncate:240|escape:'htmlall'}" />
    <meta property="og:image" content="{$sArticle.image.source}" />

    <meta property="product:brand" content="{$sArticle.supplierName}" />
    <meta property="product:price" content="{$sArticle.price}" />
    <meta property="product:product_link" content="{url sArticle=$sArticle.articleID title=$sArticle.articleName}" />

    <meta name="twitter:card" content="product" />
    <meta name="twitter:site" content="{config name=sShopname}" />
    <meta name="twitter:title" content="{$sArticle.articleName|escape:'htmlall'}" />
    <meta name="twitter:description" content="{$sArticle.description_long|strip_tags|truncate:240|escape:'htmlall'}" />
    <meta name="twitter:image" content="{$sArticle.image.source}" />
{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sArticle.keywords}{$sArticle.keywords}{elseif $sArticle.sDescriptionKeywords}{$sArticle.sDescriptionKeywords}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sArticle.description}{$sArticle.description|escape}{else}{$sArticle.description_long|strip_tags|escape}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    <link rel="canonical" href="{url sArticle=$sArticle.articleID title=$sArticle.articleName}" />
{/block}