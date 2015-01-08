{if $viewedArticles}
    {block name="frontend_detail_index_similar_viewed_slider"}
        <div class="viewed--content">
            <div class="product-slider" data-product-slider="true">
                {block name="frontend_detail_index_similar_viewed_slider_inner"}
                    <div class="product-slider--container">
                        {foreach $viewedArticles as $article}
                            <div class="product-slider--item">
                                {include file="frontend/listing/box_article.tpl" sArticle=$article productBoxLayout="slider"}
                            </div>
                        {/foreach}
                    </div>
                {/block}
            </div>
        </div>
    {/block}
{/if}