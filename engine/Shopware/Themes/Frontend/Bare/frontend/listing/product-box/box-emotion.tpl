{extends file="frontend/listing/product-box/box-basic.tpl"}

{namespace name="frontend/listing/box_article"}

{block name='frontend_listing_box_article_rating'}{/block}

{block name='frontend_listing_box_article_description'}{/block}

{block name='frontend_listing_box_article_actions'}{/block}

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

{block name='frontend_listing_box_article_badges'}
    {if !$imageOnly}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_listing_box_article_name'}
    {if !$imageOnly}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_listing_box_article_price_info'}
    {if !$imageOnly}
        {$smarty.block.parent}
    {/if}
{/block}