{extends file='frontend/index/header.tpl'}

{block name="frontend_index_header_meta_keywords"}{/block}

{block name='frontend_index_header_meta_robots'}{$sType->getSeoRobots()}{/block}

{block name='frontend_index_header_meta_tags_opengraph'}
    {s name="IndexMetaDescriptionStandard" namespace="frontend/index/header" assign="description"}{/s}

    {$contentTypeDescription = ''|snippet:'IndexMetaDescription':$sType->getSnippetNamespaceFrontend()}
    {$contentTypeImage = ''|snippet:'IndexMetaImage':$sType->getSnippetNamespaceFrontend()}
    {$contentTypeTitle = ''|snippet:'IndexMetaTitle':$sType->getSnippetNamespaceFrontend()}

    {if $contentTypeDescription}
        {$description = $contentTypeDescription}
    {/if}

    {if !$contentTypeTitle}
        {$contentTypeTitle = $sType->getName()}
    {/if}

    {$description = $description|truncate:$SeoDescriptionMaxLength:'…'}

    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{config name=sShopname}|escapeHtml}" />
    <meta property="og:title" content="{$contentTypeTitle|escapeHtml}" />
    <meta property="og:description" content="{$description|escapeHtml}" />

    <meta name="twitter:card" content="website" />
    <meta name="twitter:site" content="{{config name=sShopname}|escapeHtml}" />
    <meta name="twitter:title" content="{$contentTypeTitle|escapeHtml}" />
    <meta name="twitter:description" content="{$description|escapeHtml}" />

    {* Images *}
    {if !$contentTypeImage}
        {foreach $sItems as $sItem}
            {if $sItem@first}
                {$image = $sItem[$sImageKey]}
                {if $image[0]}
                    {* Image-Slider, take first image *}
                    {$image = $image[0]}
                {/if}

                {$contentTypeImage = $image.source}
                {break}
            {/if}
        {/foreach}
    {/if}

    <meta property="og:image" content="{$contentTypeImage}" />
    <meta name="twitter:image" content="{$contentTypeImage}" />
{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{strip}
    {s name="IndexMetaDescriptionStandard" namespace="frontend/index/header" assign="description"}{/s}
    {$contentTypeDescription = ''|snippet:'IndexMetaDescription':$sType->getSnippetNamespaceFrontend()}

    {if $contentTypeDescription}
        {$description = $contentTypeDescription}
    {/if}

    {$description = $description|truncate:$SeoDescriptionMaxLength:'…'}
    {$description}
{/strip}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
    {if $SeoMetaRobots|strpos:'noindex' === false}
        {if empty($smarty.get.p)}
            <link rel="canonical" href="{url controller=$Controller action=index}"/>
        {else}
            <link rel="canonical" href="{url controller=$Controller action=index p=$sPage}"/>
        {/if}
    {/if}

    {if {config name=seoIndexPaginationLinks}}
        {* Previous rel tag *}
        {if $sPage > 1}
            <link rel="prev" href="{url controller=$Controller action=index p=$sPage-1}">
        {/if}

        {* Next rel tag *}
        {if $pages >= $sPage + 1}
            <link rel="next" href="{url controller=$Controller action=index p=$sPage+1}">
        {/if}
    {/if}
{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}{/block}
