{namespace name="frontend/detail/related"}

{if $sArticle.sRelatedArticles && !$sArticle.crossbundlelook}
	{* Related products - Content *}
	{block name="frontend_detail_index_similar_slider_content"}
        <div class="related--content">
            <div class="product-slider" data-product-slider="true">
                <div class="product-slider--container">
                    {foreach $sArticle.sRelatedArticles as $sArticleSub}
                        <div class="product-slider--item">
                            {include file="frontend/listing/box_article.tpl" sArticle=$sArticleSub productBoxLayout="slider"}
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
	{/block}
{/if}