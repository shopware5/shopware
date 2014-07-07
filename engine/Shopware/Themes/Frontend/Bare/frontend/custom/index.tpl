{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
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

{block name="frontend_index_header"}
	{include file="frontend/custom/header.tpl"}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="custom-page--content content block">

		{* Custom page container *}
		{block name="frontend_custom_content"}
			<div class="custom-page--container" data-tab-content="true" data-mode="remote">

				{* Custom page headline*}
				{block name="frontend_cusrton"}
					<h1 class="custom-page--headline panel--title">
						{if $sCustomPage.parent}
							{$sCustomPage.parent.description}
						{else}
							{$sCustomPage.description}
						{/if}
					</h1>
				{/block}

				{* Custom page tab navigation *}
				{block name="frontend_custom_tab_navigation"}
					<nav class="custom-page--navigation">
						{if $sCustomPage.subPages}
							{$pages = $sCustomPage.subPages}
						{elseif $sCustomPage.siblingPages}
							{$pages = $sCustomPage.siblingPages}
						{/if}
						{if $pages}
							{block name="frontend_custom_tab_navigation_list"}
								<ul class="tab--navigation panel--tab-nav">
									{foreach $pages as $subPage}
										{block name="frontend_custom_tab_navigation_entry"}
											<li class="navigation--entry">

												{block name="frontend_custom_tab_navigation_entry"}
													<a class="navigation--link{if $subPage.active} is--active{/if}" href="{url controller=custom sCustom=$subPage.id}" title="{$subPage.description}">
														{$subPage.description}
													</a>
												{/block}
											</li>
										{/block}
									{/foreach}
								</ul>
							{/block}
						{/if}
					</nav>
				{/block}

				{* Custom page tab content *}
				{block name="frontend_custom_article"}
					<div class="tabs--content-container tab--content panel has--border{if !is_array($sCustomPage.parent)} is--active-parent{/if}">

						<div class="content--custom panel--body">
							{* Custom page tab headline *}
							{block name="frontend_custom_article_headline"}
								<h1 class="custom-page--tab-headline">{$sCustomPage.description}</h1>
							{/block}

							{* Custom page tab inner content *}
							{block name="frontend_custom_article_content"}
								{$sContent}
							{/block}
						</div>
					</div>
				{/block}
			</div>
		{/block}

	</div>
{/block}

{* Sidebar left *}
{block name="frontend_index_content_left"}
	{include file="frontend/index/sidebar.tpl"}
{/block}