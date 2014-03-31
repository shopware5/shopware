{namespace name="frontend/detail/index"}

{* Previous product *}
{block name='frontend_detail_article_back'}
	{if $sArticle.sNavigation.sPrevious}
		<a href="{$sArticle.sNavigation.sPrevious.link|rewrite:$sArticle.sNavigation.sPrevious.name}" title="{$sArticle.sNavigation.sPrevious.name}" class="navigation--link link--prev">{s name='DetailNavPrevious'}Zurück{/s}</a>
	{/if}
{/block}

{* Next product *}
{block name='frontend_detail_article_next'}
	{if $sArticle.sNavigation.sNext}
		<a href="{$sArticle.sNavigation.sNext.link|rewrite:$sArticle.sNavigation.sNext.name}" title="{$sArticle.sNavigation.sNext.name}" class="navigation--link link--next">{s name='DetailNavNext'}Vor{/s}</a>
	{/if}
{/block}