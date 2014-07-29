{block name='checkout_ajax_add_title'}
    <div class="modal--title">
        {if !$sBasketInfo}{s name="AjaxAddHeader"}{/s}{else}{s name='AjaxAddHeaderError'}Hinweis:{/s}{/if}
    </div>
{/block}

{block name='checkout_ajax_add_information'}
    <div class="modal--article block-group">

        {* Article image *}
        {block name='checkout_ajax_add_information_image'}
            <div class="article--image block">
                <a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|escape}">
                    {if $sArticle.image.src}
                        <img class="image--thumbnail" src="{$sArticle.image.src.3}" alt="{$sArticle.articleName|escape}">
                    {else}
                        <img class="image--no-picture" src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name='ListingBoxNoPicture'}{/s}" />
                    {/if}
                </a>
            </div>
        {/block}

        {* Article Name *}
        {block name='checkout_ajax_add_information_name'}
            <div class="article--name block">
                <ul class="list--name list--unstyled">
                    <li class="entry--name">
                        <a class="link--name" href="{$sArticle.linkDetails}" title="{$sArticle.articleName|escape}">
                            {$sArticleName|escape|truncate:37}
                        </a>
                    </li>
                    <li class="entry--ordernumber">{s name="AjaxAddLabelOrdernumber"}{/s}: {$sArticle.ordernumber}</li>
                </ul>
            </div>
        {/block}

        {* Article price *}
        {block name='checkout_ajax_add_information_price'}
            <div class="article--price block">
                <ul class="list--price list--unstyled">
                    <li class="entry--price">{$sArticle.price|currency}</li>
                    <li class="entry--quantity">{s name="AjaxAddLabelQuantity"}{/s}: {$sArticle.quantity}</li>
                </ul>
            </div>
        {/block}
    </div>
{/block}

{block name='checkout_ajax_add_actions'}
    <div class="modal--actions">
        {* Contiune shopping *}
        {block name='checkout_ajax_add_actions_continue'}
            <a href="{$sBasket.sLastActiveArticle.link}" title="{s name='AjaxAddLinkBack'}{/s}" class="link--back btn btn--secondary is--left">
                {s name='AjaxAddLinkBack'}{/s} <i class="icon--arrow-left"></i>
            </a>
        {/block}

        {* Forward to the checkout *}
        {block name='checkout_ajax_add_actions_checkout'}
            <a href="{url action=confirm}" title="{s name='AjaxAddLinkCart'}{/s}" class="link--confirm btn btn--primary right">
                {s name='AjaxAddLinkCart'}{/s} <i class="icon--arrow-right"></i>
            </a>
        {/block}
    </div>
{/block}

{block name='checkout_ajax_add_cross_selling'}
    {if $sCrossSimilarShown|@count || $sCrossBoughtToo|@count}
        <div class="modal--cross-selling">
            <div class="panel has--border">

                {* Cross sellung title *}
                {block name='checkout_ajax_add_cross_selling_title'}
                    <div class="panel--title  is--underline">
                        {s name="AjaxAddHeaderCrossSelling"}{/s}
                    </div>
                {/block}

                {* Cross selling panel body *}
                {block name='checkout_ajax_add_cross_selling_panel'}
                    <div class="panel--body">

                        {* Cross selling product slider *}
                        {block name='checkout_ajax_add_cross_slider'}
                            <div class="product-slider" data-mode="local">
                                <div class="product-slider--container">

                                    {$sCrossSellingArticles = $sCrossBoughtToo}
                                    {if $sCrossSimilarShown && $sCrossBoughtToo|count < 1}
                                        {$sCrossSellingArticles = $sCrossSimilarShown}
                                    {/if}

                                    {* Product item *}
                                    {block name='checkout_ajax_add_cross_slider_item'}
                                        {foreach $sCrossSellingArticles as $article}
                                            <div class="product-slider--item">

                                                {* Slider item Image *}
                                                {block name='checkout_ajax_add_cross_slider_item_image'}
                                                    <div class="item--image">
                                                        <a href="{$article.linkDetails}" class="link--image" title="{$article.articleName|escape}">
                                                            {if $article.image.src.2}
                                                                <img src="{$article.image.src.2}" class="image--slider-item">
                                                            {else}
                                                                <img class="image--no-picture" src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name='ListingBoxNoPicture'}{/s}" />
                                                            {/if}
                                                        </a>
                                                    </div>
                                                {/block}

                                                {* Slider item name *}
                                                {block name='checkout_ajax_add_cross_slider_item_name'}
                                                    <div class="item--name">
                                                        <a href="{$article.linkDetails}" class="link--name" title="{$article.articleName|escape}">
                                                            {$article.articleName|escape|truncate:30}
                                                        </a>
                                                    </div>
                                                {/block}

                                                {* Slider item purchase unit *}
                                                {block name='checkout_ajax_add_cross_slider_item_price_unit'}
                                                    {if $article.purchaseunit}
                                                        <div class="item--price-unit">
                                                            <strong>{se name="SlideArticleInfoContent" namespace="frontend/plugins/recommendation/slide_articles"}{/se}:</strong> {$article.purchaseunit} {$article.sUnit.description}
                                                            {if $article.referenceunit}
                                                                ({$article.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$article.referenceunit} {$article.sUnit.description})
                                                            {/if}
                                                        </div>
                                                    {/if}
                                                {/block}

                                                {* Slider item price *}
                                                {block name='checkout_ajax_add_cross_slider_item_price'}
                                                    <div class="item--price">
                                                        <span class="price--normal {if $article.pseudoprice}price--reduced{/if}">
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
                                        {/foreach}
                                    {/block}
                                </div>
                            </div>
                        {/block}
                    </div>
                {/block}
            </div>
        </div>
    {/if}
{/block}