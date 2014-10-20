{foreach $articles as $article}
    {block name="frontend_widgets_slide_articles_item"}
        {include file="frontend/listing/product-box/box-product-slider.tpl" sArticle=$article}
    {/block}
{/foreach}