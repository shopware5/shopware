{if $boughtArticles}
    <div class="bought--content product-slider" data-product-slider="true">
        <div class="product-slider--container">
            {foreach $boughtArticles as $article}
                {include file="frontend/listing/product-box/box-product-slider.tpl" sArticle=$article}
            {/foreach}
        </div>
    </div>
{/if}