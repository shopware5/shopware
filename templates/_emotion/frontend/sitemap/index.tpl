{extends file="frontend/index/index.tpl"}

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
				<li><a href="{$categoryTree.link}" title="{$categoryTree.name}" class="active">{$categoryTree.name}</a></li>
				{if $categoryTree.sub}
					{include file="frontend/sitemap/recurse.tpl" sCategoryTree=$categoryTree.sub depth=1}
				{/if}
			</ul>
			</div>

		{/foreach}

		<div class="clear"></div>
	</div>
{/block}