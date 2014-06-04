{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name="rontend_index_start" prepend}
    {$sBreadcrumb = [['name'=>"{s name='SitemapTitle'}Sitemap{/s}", 'link'=>{url controller=sitemap}]]}
{/block}

{block name="frontend_index_content"}
    <div class="content sitemap--content block">

    <h1>{s name='SitemapHeader'}Sitemap - Alle Kategorien auf einen Blick{/s}</h1>

    {foreach $sCategoryTree as $categoryTree}

		{if $categoryTree@index % 4 == 0}
			<div class="block-group">
		{/if}

		<div class="sitemap--category block">

			<ul class="sitemap--navigation">
				<li class="sitemap--navigation-head is--bold">
					{if $categoryTree.name == 'SitemapStaticPages'}
						<a href="{$categoryTree.link}" title="{s name='SitemapStaticPages'}Statische Seiten{/s}" class="is--active">
							{s name='SitemapStaticPages'}Statische Seiten{/s}
						</a>
					{elseif $categoryTree.name == 'SitemapSupplierPages'}
						<a href="{$categoryTree.link}" title="{s name='SitemapSupplierPages'}Herstellerseiten{/s}" class="is--active">
							{s name='SitemapSupplierPages'}Herstellerseiten{/s}
						</a>
					{elseif $categoryTree.name == 'SitemapLandingPages'}
						<a href="{$categoryTree.link}" title="{s name='SitemapLandingPages'}Landingpages{/s}" class="is--active">
							{s name='SitemapLandingPages'}Landingpages{/s}
						</a>
					{else}
						<a href="{$categoryTree.link}" title="{$categoryTree.name}" class="is--active">
							{$categoryTree.name}
						</a>
					{/if}
				</li>
				{if $categoryTree.sub}
					{include file="frontend/sitemap/recurse.tpl" sCategoryTree=$categoryTree.sub depth=1}
				{/if}
			</ul>
		</div>

		{if $categoryTree@index % 4 == 3 || $categoryTree@last}
			</div>
		{/if}
    {/foreach}

    </div>
{/block}