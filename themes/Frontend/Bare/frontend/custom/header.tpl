{extends file="frontend/index/header.tpl"}

{block name='frontend_index_header_canonical'}
    <link rel="canonical" href="{url controller=custom sCustom=$sCustomPage.id}" />
{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sCustomPage.meta_keywords}{$sCustomPage.meta_keywords|escapeHtml}{else}{$smarty.block.parent}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sCustomPage.meta_description}{$sCustomPage.meta_description|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}{else}{$smarty.block.parent}{/if}{/block}
{block name="frontend_index_header_meta_description_og"}{if $sCustomPage.meta_description}{$sCustomPage.meta_description|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}{else}{$smarty.block.parent}{/if}{/block}
{block name="frontend_index_header_meta_description_twitter"}{if $sCustomPage.meta_description}{$sCustomPage.meta_description|truncate:$SeoDescriptionMaxLength:'…'|escapeHtml}{else}{$smarty.block.parent}{/if}{/block}
