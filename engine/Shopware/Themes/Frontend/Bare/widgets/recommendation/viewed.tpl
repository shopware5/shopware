{if $viewedArticles}
    {block name="frontend_detail_index_similar_viewed_slider"}
        <div class="viewed--content product-slider" data-product-slider="true">
            {block name="frontend_detail_index_similar_viewed_slider_inner"}
                <div class="product-slider--container">
                    {foreach $viewedArticles as $article}
                        {include file="frontend/listing/product-box/box-product-slider.tpl" sArticle=$article}
                    {/foreach}
                </div>
            {/block}
        </div>
    {/block}
{/if}