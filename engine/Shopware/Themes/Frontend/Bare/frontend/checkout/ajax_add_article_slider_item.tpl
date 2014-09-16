{if $article.additional_details.sConfigurator}
    {$detailLink={url controller=detail sArticle=$article.articleID number=$article.ordernumber}}
{else}
    {$detailLink=$article.linkDetails}
{/if}
<div class="product-slider--item">
    {* Slider item Image *}
    {block name='checkout_ajax_add_cross_slider_item_image'}
        <div class="item--image">
            <a href="{$detailLink}" class="link--image" title="{$article.articleName|escape}">
                <span data-picture data-alt="{if $article.image.res.description}{$article.image.res.description|escape:"html"}{else}{$article.articleName|escape:"html"}{/if}">

                    {*Image based on our default media queries*}
                    {block name='checkout_ajax_add_cross_slider_item_image_default_queries'}
                        <span data-src="{if isset($article.image.src)}{$article.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}"></span>
                    {/block}

                    {*Block to add additional image based on media queries*}
                    {block name='checkout_ajax_add_cross_slider_item_image_additional_queries'}{/block}

                    {*If the browser doesn't support JS, the following image will be used*}
                    {block name='checkout_ajax_add_cross_slider_item_image_fallback'}
                        <noscript>
                            <img itemprop="image" src="{if isset($article.image.src)}{$article.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" alt="{$article.articleName|escape:"html"}">
                        </noscript>
                    {/block}
                </span>
            </a>
        </div>
    {/block}

    {* Slider item name *}
    {block name='checkout_ajax_add_cross_slider_item_name'}
        <div class="item--name">
            <a href="{$detailLink}" class="link--name" title="{$article.articleName|escape}">
                {$article.articleName|escape|truncate:30}
            </a>
        </div>
    {/block}

    {* Slider item purchase unit *}
    {block name='checkout_ajax_add_cross_slider_item_price_unit'}
        {if $article.purchaseunit}
            <div class="item--price-unit">
                <strong class="item--price-unit--content-info">{s name="SlideArticleInfoContent" namespace="frontend/checkout/ajax_add_article"}{/s}:</strong> {$article.purchaseunit} {$article.sUnit.description}
                {if $article.referenceunit}
                    ({$article.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$article.referenceunit} {$article.sUnit.description})
                {/if}
            </div>
        {/if}
    {/block}

    {* Slider item price *}
    {block name='checkout_ajax_add_cross_slider_item_price'}
        <div class="item--price">
            <span class="price--normal{if $article.pseudoprice} price--reduced{/if}">
                {if $article.priceStartingFrom && !$article.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}
                {$article.price|currency}
                {s name="Star" namespace="frontend/listing/box_article"}{/s}
            </span>

            {if $article.pseudoprice}
                <span class="price--pseudo">{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$article.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</span>
            {/if}
        </div>
    {/block}
</div>