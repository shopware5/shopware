{if $sArticle.sSimilarArticles}
	{* Similar products - Content *}
	{block name="frontend_detail_index_similar_slider_content"}
		<div class="listing--container">
			<ul class="listing listing--listing">
				{foreach $sArticle.sSimilarArticles as $sSimilarArticle}
					{block name="frontend_detail_index_similar_slider_item"}
						{include file="frontend/listing/box_similar.tpl" sArticle=$sSimilarArticle}
					{/block}
				{/foreach}
			</ul>
		</div>
	{/block}
{/if}