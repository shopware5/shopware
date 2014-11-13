{if $viewedArticles}
    <div class="viewed--content product-slider" data-product-slider="true">
        <div class="product-slider--container">
            {foreach $viewedArticles as $article}
                {include file="widgets/recommendation/item.tpl" article=$article}
            {/foreach}
        </div>
    </div>
{/if}