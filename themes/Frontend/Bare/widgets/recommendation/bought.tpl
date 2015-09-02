{if $boughtArticles}
    {block name="frontend_detail_index_also_bought_slider"}
        <div class="bought--content panel--body">
            <div class="product-slider" data-product-slider="true">
                {block name="frontend_detail_index_also_bought_slider_inner"}
                    <div class="product-slider--container">
                        {foreach $boughtArticles as $article}
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