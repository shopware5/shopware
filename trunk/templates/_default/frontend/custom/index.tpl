{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
{$sBreadcrumb = []}
{if $sCustomPage.parent}
{$sBreadcrumb[] = [
    'name' => {$sCustomPage.parent.page_title|default:$sCustomPage.parent.description},
    'link'=>{url sCustom=$sCustomPage.parent.id}
]}
{/if}
{$sBreadcrumb[] = [
    'name' => {$sCustomPage.page_title|default:$sCustomPage.description},
    'link'=>{url sCustom=$sCustomPage.id}
]}
{/block}

{block name='frontend_index_header'}
	{include file='frontend/custom/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="custom grid_13">

	<h1>{$sCustomPage.description}</h1>
	
	{* Article content *}
	{block name='frontend_custom_article_content'}
		{$sContent}
	{/block}
</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
	{include file="frontend/custom/right.tpl"}
{/block}