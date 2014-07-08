
{extends file='frontend/index/header.tpl'}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sCustomPage.meta_keywords}{$sCustomPage.meta_keywords}{else}{$smarty.block.parent}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sCustomPage.meta_description}{$sCustomPage.meta_description}{else}{$smarty.block.parent}{/if}{/block}
