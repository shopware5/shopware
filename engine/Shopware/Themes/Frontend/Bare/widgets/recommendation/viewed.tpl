{if $viewedArticles}
    <div class="viewed--content product-slider" data-product-slider="true">
        <div class="product-slider--container">
            {foreach $viewedArticles as $article}
                {include file="frontend/listing/product-box/box-product-slider.tpl" sArticle=$article}
            {/foreach}
        </div>
    </div>
{/if}