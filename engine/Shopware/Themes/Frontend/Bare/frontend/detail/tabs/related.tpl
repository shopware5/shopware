{namespace name="frontend/detail/related"}
{if $sArticle.sRelatedArticles && !$sArticle.crossbundlelook}
	{* Related products - Title *}
	{block name="frontend_detail_index_related_slider_title"}
		<h2 class="content--title">{s name='DetailRelatedHeader'}{/s}</h2>
	{/block}

	{* Related products - Content *}
	{block name="frontend_detail_index_similar_slider_content"}
		<div class="listing--container">
			<ul class="listing listing--listing">
				{foreach $sArticle.sRelatedArticles as $sArticleSub}
					{include file="frontend/listing/box_article.tpl" sArticle=$sArticleSub sTemplate='listing'}
				{/foreach}
			</ul>
		</div>
	{/block}
{/if}