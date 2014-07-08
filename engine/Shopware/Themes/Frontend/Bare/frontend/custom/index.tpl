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
<div class="content custom-page block">

	{* Sub page navigation *}
	{block name='frontend_custom_article_navigation'}
		<nav class="custom-page--navigation">
			{if $sCustomPage.subPages}
				{$pages = $sCustomPage.subPages}
			{elseif $sCustomPage.siblingPages}
				{$pages = $sCustomPage.siblingPages}
			{/if}
			{if $pages}
				{block name='frontend_custom_article_navigation_list'}
					<ul class="navigation--list">
						{foreach $pages as $subPage}
							{block name='frontend_custom_article_navigation_entry'}
								<li class="list--entry">
									<a class="entry--link" href="{url controller=custom sCustom=$subPage.id}" title="{$subPage.description}"{if $subPage.active} class="is--active"{/if}>
										{$subPage.description}
									</a>
								</li>
							{/block}
						{/foreach}
					</ul>
				{/block}
			{/if}
		</nav>
	{/block}

	{* Custom page headline *}
	{block name='frontend_custom_article_headline'}
		<h1 class="custom-page--headline">{$sCustomPage.description}</h1>
	{/block}

	{* Custom page content *}
	{block name='frontend_custom_article_content'}
		{$sContent}
	{/block}
</div>
{/block}

{* Sidebar left *}
{block name='frontend_index_content_left'}
	{include file='frontend/index/sidebar.tpl'}
{/block}