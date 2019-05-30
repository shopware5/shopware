<div class="modal--checkout-add-article">
    {block name='checkout_ajax_add_title'}
        <div class="modal--title">
            {if !$sBasketInfo}{s name="AjaxAddHeader"}{/s}{else}{s name='AjaxAddHeaderError'}{/s}{/if}
        </div>
    {/block}

    {block name='checkout_ajax_add_error'}
        {if $sBasketInfo}
            <div class="modal--error">
                {include file="frontend/_includes/messages.tpl" type="info" content="{$sBasketInfo}"}
            </div>
        {/if}
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
                    <a href="{$detailLink}" class="link--article-image" title="{$sArticle.articlename|escape}">

                        {$image = $sArticle.additional_details.image}
                        {$alt = $sArticle.articlename|escape}

                        {if $image.description}
                            {$alt = $image.description|escape}
                        {/if}

                        <span class="image--media">
                            {if isset($image.thumbnails)}
                                <img srcset="{$image.thumbnails[0].sourceSet}" alt="{$alt}" title="{$alt|truncate:160}" />
                            {else}
                                {block name='frontend_detail_image_fallback'}
                                    <img src="{link file='frontend/_public/src/img/no-picture.jpg'}" alt="{$alt}" title="{$alt|truncate:160}" />
                                {/block}
                            {/if}
                        </span>
                    </a>
                </div>
            {/block}

            <div class="article--info">
                {* Article Name *}
                {block name='checkout_ajax_add_information_name'}
                    <div class="article--name">
                        <ul class="list--name list--unstyled">
                            <li class="entry--name">
                                <a class="link--name" href="{$detailLink}" title="{$sArticle.articlename|escape}">
                                    {$sArticle.articlename|escape|truncate:35}
                                </a>
                            </li>
                            <li class="entry--ordernumber">{s name="AjaxAddLabelOrdernumber"}{/s}: {$sArticle.ordernumber}</li>

                            {block name='checkout_ajax_add_information_essential_features'}
                                {if {config name=alwaysShowMainFeatures}}
                                    <div class="product--essential-features">
                                        {$sBasketItem = $sArticle}
                                        {include file="string:{config name=mainfeatures}"}
                                    </div>
                                {/if}
                            {/block}
                        </ul>
                    </div>
                {/block}

                {* Article price *}
                {block name='checkout_ajax_add_information_price'}
                    <div class="article--price">
                        <ul class="list--price list--unstyled">
                            <li class="entry--price">{$sArticle.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</li>
                            <li class="entry--quantity">{s name="AjaxAddLabelQuantity"}{/s}: {$sArticle.quantity}</li>
                        </ul>
                    </div>
                {/block}
            </div>
        </div>
    {/block}

    {block name='checkout_ajax_add_actions'}
        <div class="modal--actions">
            {* Contiune shopping *}
            {block name='checkout_ajax_add_actions_continue'}
                <a href="{$detailLink}" data-modal-close="true" title="{s name='AjaxAddLinkBack'}{/s}" class="link--back btn is--secondary is--left is--icon-left is--large">
                    {s name='AjaxAddLinkBack'}{/s} <i class="icon--arrow-left"></i>
                </a>
            {/block}

            {* Forward to the checkout *}
            {block name='checkout_ajax_add_actions_checkout'}
                <a href="{url action=cart}" title="{s name='AjaxAddLinkCart'}{/s}" class="link--confirm btn is--primary right is--icon-right is--large">
                    {s name='AjaxAddLinkCart'}{/s} <i class="icon--arrow-right"></i>
                </a>
            {/block}
        </div>
    {/block}

    {block name='checkout_ajax_add_cross_selling'}
        {if $sCrossSimilarShown|@count || $sCrossBoughtToo|@count}
            <div class="modal--cross-selling">
                <div class="panel has--border is--rounded">

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
                                {if $sCrossBoughtToo|count < 1 && $sCrossSimilarShown}
                                    {$sCrossSellingArticles = $sCrossSimilarShown}
                                {else}
                                    {$sCrossSellingArticles = $sCrossBoughtToo}
                                {/if}

                                {include file="frontend/_includes/product_slider.tpl" articles=$sCrossSellingArticles}
                            {/block}
                        </div>
                    {/block}
                </div>
            </div>
        {/if}
    {/block}
</div>
