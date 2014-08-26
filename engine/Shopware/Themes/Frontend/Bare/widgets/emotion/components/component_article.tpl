{$sArticle = $Data}
{$colSpan = $element.endCol - $element.startCol + 1}

{block name="widget_emotion_component_product_panel"}
    <div class="product--box panel--body has--border element-width--{$colSpan}">

        {* Product image - uses the picturefill polyfill for the HTML5 "picture" element *}
        {block name="widget_emotion_component_product_image"}
            <a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName|escape:'html'}" class="box--image">
                <span data-picture data-alt="{$sArticle.articleName|escape:'html'}" class="image--element">
                    <span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no_picture.jpg'}{/if}"></span>
                    <span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.3}{else}{link file='frontend/_public/src/img/no_picture.jpg'}{/if}" data-media="(min-width: 24.375em)"></span>
                    <span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no_picture.jpg'}{/if}" data-media="(min-width: 35em)"></span>
                    <noscript>
                        <img src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no_picture.jpg'}{/if}" alt="{$sArticle.articleName}">
                    </noscript>
                </span>
            </a>
        {/block}

        {* Product title *}
        {block name="widget_emotion_component_product_title"}
            <a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" class="product--title"
               title="{$sArticle.articleName|escape:'html'}">{$sArticle.articleName|truncate:55}</a>
        {/block}

        <div class="product--price">

            {* Product price - Unit price *}
            {block name='widget_emotion_component_product_unit'}
                {include file="frontend/listing/product-box/unit-price.tpl"}
            {/block}

            {* Product price - Default and discount price *}
            {block name='widget_emotion_component_product_price'}
                {include file="frontend/listing/product-box/price.tpl"}
            {/block}
        </div>
    </div>
{/block}