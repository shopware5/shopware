{extends file="frontend/index/header.tpl"}

{* title *}
{block name="frontend_index_header_title"}{if $sSupport.metaTitle}{$sSupport.metaTitle} | {config name=sShopname}{else}{$smarty.block.parent}{/if}{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sSupport.metaKeywords}{$sSupport.metaKeywords}{else}{$smarty.block.parent}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sSupport.metaDescription}{$sSupport.metaDescription}{else}{$smarty.block.parent}{/if}{/block}