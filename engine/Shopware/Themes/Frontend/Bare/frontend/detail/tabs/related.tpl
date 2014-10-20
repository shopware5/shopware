{namespace name="frontend/detail/related"}

{if $sArticle.sRelatedArticles && !$sArticle.crossbundlelook}
	{* Related products - Content *}
	{block name="frontend_detail_index_similar_slider_content"}
		<div class="related--content product-slider" data-product-slider="true">
			<div class="product-slider--container">
				{foreach $sArticle.sRelatedArticles as $sArticleSub}
                    {include file="frontend/listing/product-box/box-product-slider.tpl" sArticle=$sArticleSub}
				{/foreach}
			</div>
		</div>
	{/block}
{/if}