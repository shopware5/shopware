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
	{if $sCustomPage.subPages}
		{$pages = $sCustomPage.subPages}
	{elseif $sCustomPage.siblingPages}
		{$pages = $sCustomPage.siblingPages}
	{/if}
	{if $pages}
		<div class="custom_subnavi">
			{if $pages}
				<ul class="sub-pages">
				{foreach $pages as $subPage}
					<li><a href="{url controller=custom sCustom=$subPage.id}" title="{$subPage.description}" {if $subPage.active} class="active"{/if}>
						{$subPage.description}
					</a></li>
				{/foreach}
				</ul>
			{/if}
		</div>
	{/if}

	<div id="center" class="custom grid_13">

		<h1>{$sCustomPage.description}</h1>

		{* Article content *}
		{block name='frontend_custom_article_content'}
			{$sContent}
		{/block}
	</div>
{/block}

{* Hide sidebar right *}
{block name='frontend_index_content_right'}{/block}

{* Sidebar left *}
{block name='frontend_index_content_left'}
	{include file='frontend/index/left.tpl'}
{/block}