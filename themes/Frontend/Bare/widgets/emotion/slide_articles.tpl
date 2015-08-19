{foreach $articles as $article}
    {block name="frontend_widgets_slide_articles_item"}

        {$boxLayout = 'emotion'}

        {if $productBoxLayout}
            {$boxLayout = $productBoxLayout}
        {/if}

        <div class="product-slider--item">
            {include file="frontend/listing/box_article.tpl" sArticle=$article productBoxLayout=$boxLayout}
        </div>
    {/block}
{/foreach}