{if $boughtArticles}
    {block name="frontend_detail_index_also_bought_slider"}
        <div class="bought--content product-slider" data-product-slider="true">
            {block name="frontend_detail_index_also_bought_slider_inner"}
                <div class="product-slider--container">
                    {foreach $boughtArticles as $article}
                        {include file="frontend/listing/product-box/box-product-slider.tpl" sArticle=$article}
                    {/foreach}
                </div>
            {/block}
        </div>
    {/block}
{/if}