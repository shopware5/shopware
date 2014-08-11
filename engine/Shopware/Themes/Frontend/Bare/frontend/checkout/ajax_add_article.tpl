<div class="modal--checkout-add-article">
    {block name='checkout_ajax_add_title'}
        <div class="modal--title">
            {if !$sBasketInfo}{s name="AjaxAddHeader"}{/s}{else}{s name='AjaxAddHeaderError'}Hinweis:{/s}{/if}
        </div>
    {/block}

    {block name='checkout_ajax_add_information'}
        {if $sArticle.additional_details.sConfigurator}
            {$detailLink={url controller=detail sArticle=$sArticle.articleID number=$sArticle.ordernumber}}
        {else}
            {$detailLink=$sArticle.linkDetails}
        {/if}

        <div class="modal--article block-group">

            {* Article image *}
            {block name='checkout_ajax_add_information_image'}
                <div class="article--image block">
                    <a href="{$detailLink}" class="link--article-image" title="{$sArticle.articlename|escape:"html"}">
                        <span data-picture data-alt="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articlename|escape:"html"}{/if}">
                            {*Image based on our default media queries*}
                            {block name='frontend_detail_image_default_queries'}
                                <span data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.2}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
                            {/block}

                            {*Block to add additional image based on media queries*}
                            {block name='frontend_detail_image_additional_queries'}{/block}

                            {*If the browser doesn't support JS, the following image will be used*}
                            {block name='frontend_detail_image_fallback'}
                                <noscript>
                                    <img src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$sArticle.articleName|escape:"html"}">
                                </noscript>
                            {/block}
                        </span>
                    </a>
                </div>
            {/block}

            {* Article Name *}
            {block name='checkout_ajax_add_information_name'}
                <div class="article--name block">
                    <ul class="list--name list--unstyled">
                        <li class="entry--name">
                            <a class="link--name" href="{$detailLink}" title="{$sArticle.articlename|escape}">
                                {$articlename|escape|truncate:37}
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
                        <li class="entry--price">{$sArticle.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</li>
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
                <a href="{$detailLink}" title="{s name='AjaxAddLinkBack'}{/s}" class="link--back btn btn--secondary is--left">
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
                        <div class="panel--title is--underline">
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

                                        {if $sCrossBoughtToo|count < 1 && $sCrossSimilarShown}
                                            {$sCrossSellingArticles = $sCrossSimilarShown}
                                        {else}
                                            {$sCrossSellingArticles = $sCrossBoughtToo}
                                        {/if}

                                        {* Product item *}
                                        {foreach $sCrossSellingArticles as $article}
                                            {block name='checkout_ajax_add_cross_slider_item'}
                                                {include file="frontend/checkout/ajax_add_article_slider_item.tpl"}
                                            {/block}
                                        {/foreach}
                                    </div>
                                </div>
                            {/block}
                        </div>
                    {/block}
                </div>
            </div>
        {/if}
    {/block}
</div>