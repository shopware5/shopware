{foreach $articles as $article}
    {block name="frontend_widgets_slide_articles_item"}
        <div class="product-slider--item">
            {include file="frontend/listing/box_article.tpl" sArticle=$article productBoxLayout="emotion"}
        </div>
    {/block}
{/foreach}