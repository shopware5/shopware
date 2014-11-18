{if $sArticle.sSimilarArticles}
	{* Similar products - Content *}
	{block name="frontend_detail_index_similar_slider_content"}
		<div class="similar--content product-slider" data-product-slider="true">
			<div class="product-slider--container">
				{foreach $sArticle.sSimilarArticles as $sSimilarArticle}
					{block name="frontend_detail_index_similar_slider_item"}
                        {include file="frontend/listing/product-box/box-product-slider.tpl" sArticle=$sSimilarArticle}
					{/block}
				{/foreach}
			</div>
		</div>
	{/block}
{/if}