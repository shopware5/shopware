{namespace name="frontend/detail/index"}

{* Previous product *}
{block name='frontend_detail_article_back'}
	{if $sArticle.sNavigation.sPrevious}
		<div class="navigation--link link--prev">
			<a href="{$sArticle.sNavigation.sPrevious.link|rewrite:$sArticle.sNavigation.sPrevious.name}" title="{$sArticle.sNavigation.sPrevious.name}" class="article_back">{s name='DetailNavPrevious'}Zurück{/s}</a>
		</div>
	{/if}
{/block}

{* Next product *}
{block name='frontend_detail_article_next'}
	{if $sArticle.sNavigation.sNext}
		<div class="navigation--link link--next">
			<a href="{$sArticle.sNavigation.sNext.link|rewrite:$sArticle.sNavigation.sNext.name}" title="{$sArticle.sNavigation.sNext.name}" class="article_next">{s name='DetailNavNext'}Vor{/s}</a>
		</div>
	{/if}
{/block}