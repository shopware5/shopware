
{* Bundlebox  *}
{if $sArticle.sRelatedArticles && $sArticle.crossbundlelook || $sArticle.sBundles}
	{if !$sArticle.sVariants}
		<div class="bundle">
			<div class="space">&nbsp;</div>
			{if (0 == $sArticle.laststock) || (1 == $sArticle.laststock && $sArticle.instock > 0)}
				{include file="frontend/detail/bundle/box_bundle.tpl" sBundles=$sArticle.sBundles} 
			{/if}
		</div>
	{else}
		<div class="bundle">
			<div class="space">&nbsp;</div>
			{include file="frontend/detail/bundle/box_bundle.tpl" sBundles=$sArticle.sBundles}
		</div>
	{/if}
	
	{* Related article box *}
	{if $sArticle.sRelatedArticles && $sArticle.crossbundlelook}
		<div class="bundle">
			<div class="space">&nbsp;</div>
			{include file="frontend/detail/bundle/box_related.tpl" sRelatedArticles=$sArticle.sRelatedArticles}
		</div>
	{/if}
{/if}
