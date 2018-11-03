{namespace name="frontend/listing/box_article"}

{block name="frontend_listing_box_article"}
    <div class="product--box box--{$productBoxLayout}" data-ordernumber="{$sArticle.ordernumber}">

        {block name="frontend_listing_box_article_product_name"}
            {$productName = $sArticle.articleName}
            {if $sArticle.additionaltext}
                {$productName = $productName|cat:' '|cat:$sArticle.additionaltext}
            {/if}
        {/block}

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
                            <a href="{$sArticle.linkDetails}"
                               title="{$productName|escape}"
                               class="product--image{if $imageOnly} is--large{/if}">

                                {block name='frontend_listing_box_article_image_element'}
                                    <span class="image--element">

                                        {block name='frontend_listing_box_article_image_media'}
                                            <span class="image--media">

                                                {block name='frontend_listing_box_article_image_picture'}

                                                    {$desc = $productName|escape}

                                                    {if $sArticle.image.description}
                                                        {$desc = $sArticle.image.description|escape}
                                                    {/if}

                                                    {if $sArticle.image.thumbnails}

                                                        {if $element.viewports && !$fixedImageSize}
                                                            {foreach $element.viewports as $viewport}
                                                                {$cols = ($viewport.endCol - $viewport.startCol) + 1}
                                                                {$elementSize = $cols * $cellWidth}
                                                                {$size = "{$elementSize}vw"}

                                                                {if $breakpoints[$viewport.alias]}

                                                                    {if $viewport.alias === 'xl' && !$emotionFullscreen}
                                                                        {$size = "calc({$elementSize / 100} * {$baseWidth}px)"}
                                                                        {$size = "(min-width: {$baseWidth}px) {$size}"}
                                                                    {else}
                                                                        {$size = "(min-width: {$breakpoints[$viewport.alias]}) {$size}"}
                                                                    {/if}
                                                                {/if}

                                                                {$itemSize = "{$size}{if $itemSize}, {$itemSize}{/if}"}
                                                            {/foreach}
                                                        {else}
                                                            {$itemSize = "200px"}
                                                        {/if}

                                                        {$srcSet = ''}
                                                        {$srcSetRetina = ''}

                                                        {foreach $sArticle.image.thumbnails as $image}
                                                            {$srcSet = "{if $srcSet}{$srcSet}, {/if}{$image.source} {$image.maxWidth}w"}

                                                            {if $image.retinaSource}
                                                                {$srcSetRetina = "{if $srcSetRetina}{$srcSetRetina}, {/if}{$image.retinaSource} {$image.maxWidth * 2}w"}
                                                            {/if}
                                                        {/foreach}

                                                        <picture>
                                                            <source sizes="{$itemSize}" srcset="{$srcSetRetina}" media="(min-resolution: 192dpi)" />
                                                            <source sizes="{$itemSize}" srcset="{$srcSet}" />

                                                            <img src="{$sArticle.image.thumbnails[0].source}" alt="{$desc|strip_tags|truncate:160}" />
                                                        </picture>

                                                    {elseif $sArticle.image.source}
                                                        <img src="{$sArticle.image.source}" alt="{$desc|strip_tags|truncate:160}" />
                                                    {else}
                                                        <img src="{link file='frontend/_public/src/img/no-picture.jpg'}" alt="{$desc|strip_tags|truncate:160}" />
                                                    {/if}
                                                {/block}
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
                                    <a href="{$sArticle.linkDetails}"
                                       class="product--title"
                                       title="{$productName|escapeHtml}">
                                        {$productName|truncate:50|escapeHtml}
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
