{if $sArticle.sSimilarArticles}

	{* Similar products - Title *}
	{block name="frontend_detail_index_similar_slider_title"}
		<h3 class="content--title">
			{s name='DetailSimilarHeader'}{/s}
		</h3>
	{/block}

	{* Similar products - Content *}
	{block name="frontend_detail_index_similar_slider_content"}
		<div class="listing--container">
			<ul class="listing listing--listing">
				{foreach $sArticle.sSimilarArticles as $sSimilarArticle}
					{include file="frontend/listing/box_similar.tpl" sArticle=$sSimilarArticle}
				{/foreach}
			</ul>
		</div>
	{/block}
{/if}