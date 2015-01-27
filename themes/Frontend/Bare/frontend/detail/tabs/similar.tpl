{if $sArticle.sSimilarArticles}
	{* Similar products - Content *}
	{block name="frontend_detail_index_similar_slider_content"}
        <div class="similar--content">
            <div class="product-slider" data-product-slider="true">
                <div class="product-slider--container">
                    {foreach $sArticle.sSimilarArticles as $sSimilarArticle}
                        {block name="frontend_detail_index_similar_slider_item"}
                            <div class="product-slider--item">
                                {include file="frontend/listing/box_article.tpl" sArticle=$sSimilarArticle productBoxLayout="slider"}
                            </div>
                        {/block}
                    {/foreach}
                </div>
            </div>
        </div>
	{/block}
{/if}