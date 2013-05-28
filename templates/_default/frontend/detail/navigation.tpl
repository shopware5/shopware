{* Article navigation *}

<div class="grid_16 menu_top">
	{block name='frontend_detail_menu_top'}
	
	{* Back to listing *}
	{if !{config name=disableArticleNavigation}}
		<div class="article_overview">
			{$sArticle.sNavigation.sCurrent.position} {s name='DetailNavCount'}{/s} {$sArticle.sNavigation.sCurrent.count}
			(<a href="{$sArticle.sNavigation.sCurrent.sCategoryLink|rewrite:$sArticle.sNavigation.sCurrent.sCategoryName}" title="{$sArticle.sNavigation.sCurrent.sCategoryName}">{s name='DetailNavIndex'}{/s}</a>)
		</div>
	{/if}
	<div class="article_navi">
		{* Previous article *}
		<div class="article_back">
			{if $sArticle.sNavigation.sPrevious}
				<a href="{$sArticle.sNavigation.sPrevious.link|rewrite:$sArticle.sNavigation.sPrevious.name}" title="{$sArticle.sNavigation.sPrevious.name}" class="article_back">{s name='DetailNavPrevious'}{/s}</a>
			{/if}
		</div>
		
		{* Next article *}
		<div class="article_next">
			{if $sArticle.sNavigation.sNext}
				<a href="{$sArticle.sNavigation.sNext.link|rewrite:$sArticle.sNavigation.sNext.name}" title="{$sArticle.sNavigation.sNext.name}" class="article_next">{s name='DetailNavNext'}{/s}</a>
			{/if}
		</div>
		{/block}
	</div>
	
	<div class="clear"></div>	
</div>