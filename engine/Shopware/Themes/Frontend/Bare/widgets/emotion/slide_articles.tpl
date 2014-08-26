{foreach $articles as $article}
    {block name="frontend_widgets_slide_articles_item"}
        <div class="product-slider--item article-slider--item">
            <div class="is--centered">

                {block name="frontend_widgets_slide_articles_item_image"}
                    <a href="{$article.linkDetails|rewrite:$article.articleName}" title="{$article.articleName|escape:'html'}" class="product--image">
                        <span data-picture data-alt="{$article.articleName|escape:'html'}" class="image--element">
                            <span class="image--media" data-src="{if isset($article.image.src)}{$article.image.src.3}{else}{link file='frontend/_public/src/img/no_picture.jpg'}{/if}"></span>
                            <span class="image--media" data-src="{if isset($article.image.src)}{$article.image.src.4}{else}{link file='frontend/_public/src/img/no_picture.jpg'}{/if}" data-media="(min-width: 78.75em)"></span>

                            <noscript>
                                <img src="{if isset($article.image.src)}{$article.image.src.2}{else}{link file='frontend/_public/src/img/no_picture.jpg'}{/if}" alt="{$article.articleName}" />
                            </noscript>
                        </span>
                    </a>
                {/block}

                <a title="{$article.articleName|escape:'html'}" class="product--title" href="{$article.linkDetails}">{$article.articleName|truncate:26}</a>
            </div>
        </div>
    {/block}
{/foreach}