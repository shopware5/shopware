{extends file="frontend/listing/product-box/box-basic.tpl"}

{namespace name="frontend/listing/box_article"}

{block name="frontend_listing_box_article"}
    <div class="product--box box--{$productBoxLayout}" data-ordernumber="{$sArticle.ordernumber}">

        {block name="frontend_listing_box_article_content"}
            <div class="box--content">

                {* Product badges *}
                {block name='frontend_listing_box_article_badges'}
                    {if !$imageOnly}
                        {include file="frontend/listing/product-box/product-badges.tpl"}
                    {/if}
                {/block}

                {block name='frontend_listing_box_article_info_container'}
                    <div class="product--info">

                        {* Product image *}
                        {block name='frontend_listing_box_article_picture'}
                            <a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}"
                               title="{$sArticle.articleName|escape:'html'}"
                               class="product--image{if $imageOnly} is--large{/if}">

                                {block name='frontend_listing_box_article_image_element'}
                                    <span class="image--element">

                                        {block name='frontend_listing_box_article_image_media'}
                                            <span class="image--media">
                                                {if isset($sArticle.image.thumbnails)}
                                                    {block name='frontend_listing_box_article_image_picture_element'}
                                                        <picture>
                                                            <source srcset="{$sArticle.image.thumbnails[2].sourceSet}" media="(min-width: 78em)">
                                                            <source srcset="{$sArticle.image.thumbnails[1].sourceSet}" media="(min-width: 48em)">

                                                            <img srcset="{$sArticle.image.thumbnails[0].sourceSet}" alt="{$sArticle.articleName|escape:'html'}" />
                                                        </picture>
                                                    {/block}
                                                {else}
                                                    <img src="{link file='frontend/_public/src/img/no-picture.jpg'}" alt="{$sArticle.articleName|escape:'html'}" />
                                                {/if}
                                            </span>
                                        {/block}
                                    </span>
                                {/block}
                            </a>
                        {/block}

                        {if !$imageOnly}
                            <div class="product--details">

                                {* Product name *}
                                {block name='frontend_listing_box_article_name'}
                                    <a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}"
                                       class="product--title"
                                       title="{$sArticle.articleName|escape}">
                                        {$sArticle.articleName|truncate:50}
                                    </a>
                                {/block}

                                {block name='frontend_listing_box_article_price_info'}
                                    <div class="product--price-info">

                                        {* Product price - Unit price *}
                                        {block name='frontend_listing_box_article_unit'}
                                            {include file="frontend/listing/product-box/product-price-unit.tpl"}
                                        {/block}

                                        {* Product price - Default and discount price *}
                                        {block name='frontend_listing_box_article_price'}
                                            {include file="frontend/listing/product-box/product-price.tpl"}
                                        {/block}
                                    </div>
                                {/block}
                            </div>
                        {/if}
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}
