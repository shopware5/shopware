{extends file="frontend/listing/product-box/box-basic.tpl"}

{namespace name="frontend/listing/box_article"}

{block name='frontend_listing_box_article_description'}{/block}

{block name='frontend_listing_box_article_picture'}
    <a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}"
       title="{$sArticle.articleName|escape}"
       class="product--image">
        {block name='frontend_listing_box_article_image_element'}
            <span class="image--element">
            {block name='frontend_listing_box_article_image_media'}
                <span class="image--media">
                    {if isset($sArticle.image.thumbnails)}
                        {block name='frontend_listing_box_article_image_picture_element'}
                            <img srcset="{$sArticle.image.thumbnails[1].sourceSet}"
                                 alt="{$sArticle.articleName|escape}"
                                 title="{$sArticle.articleName|escape|truncate:25:""}" />
                        {/block}
                    {else}
                        <img src="{link file='frontend/_public/src/img/no-picture.jpg'}"
                             alt="{$sArticle.articleName|escape}"
                             title="{$sArticle.articleName|escape|truncate:25:""}" />
                    {/if}
                </span>
            {/block}
        </span>
        {/block}
    </a>
{/block}