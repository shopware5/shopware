{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
    {$sBreadcrumb = [['name'=>"{s name='SitemapTitle'}Sitemap{/s}", 'link'=>{url controller=sitemap}]]}
{/block}

{block name="frontend_index_content"}
	<div class="grid_16 sitemap" id="center">

		<h1>{s name='SitemapHeader'}Sitemap - Alle Kategorien auf einen Blick{/s}</h1>

		{foreach from=$sCategoryTree item=categoryTree name="sitemapNumber"}

			{if $smarty.foreach.sitemapNumber.last==TRUE}
				<div class="sitemap2">
			{else}
				<div class="sitemap">
			{/if}

			<ul id="categories_s">
				<li>
                    {if $categoryTree.name == 'SitemapStaticPages'}
                        <a href="{$categoryTree.link}" title="{s name='SitemapStaticPages'}Statische Seiten{/s}" class="active">
                            {s name='SitemapStaticPages'}Statische Seiten{/s}
                        </a>
                    {elseif $categoryTree.name == 'SitemapSupplierPages'}
                        <a href="{$categoryTree.link}" title="{s name='SitemapSupplierPages'}Herstellerseiten{/s}" class="active">
                            {s name='SitemapSupplierPages'}Herstellerseiten{/s}
                        </a>
                    {elseif $categoryTree.name == 'SitemapLandingPages'}
                        <a href="{$categoryTree.link}" title="{s name='SitemapLandingPages'}Landingpages{/s}" class="active">
                            {s name='SitemapLandingPages'}Landingpages{/s}
                        </a>
                    {else}
                        <a href="{$categoryTree.link}" title="{$categoryTree.name}" class="active">
                            {$categoryTree.name}
                        </a>
                    {/if}
                </li>
				{if $categoryTree.sub}
					{include file="frontend/sitemap/recurse.tpl" sCategoryTree=$categoryTree.sub depth=1}
				{/if}
			</ul>
			</div>

		{/foreach}

		<div class="clear"></div>
	</div>
{/block}