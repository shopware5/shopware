{if $Data}
    {block name="widget_emotion_component_category_teaser_panel"}
        <div class="category-teaser--box panel--box">

            {* Category teaser image *}
            {block name="widget_emotion_component_category_teaser_link"}
                <a class="box--image" href="{if $Data.blog_category}{url controller=blog action=index sCategory=$Data.category_selection}{else}{url controller=cat action=index sCategory=$Data.category_selection}{/if}" title="{$Data.categoryName|strip_tags}">

                    {if $Data.image_type === 'selected_image'}
                        {block name="widget_emotion_component_category_teaser_image"}
                            <img src="{$Data.image}" alt="{$Data.categoryName|escape}" class="selected-image--element">
                        {/block}
                    {else}
                        {block name="widget_emotion_component_category_teaser_product_image"}
                            <span data-picture data-alt="{"{config name=shopName}"|escape} - {"{s name='IndexLinkDefault' namespace="frontend/index/index"}{/s}"|escape}" class="image--element">
                                <span data-src="{if isset($Data.images)}{$Data.images.2}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}"></span>
                                <span data-src="{if isset($Data.images)}{$Data.images.3}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" data-media="(min-width: 47.75em)"></span>
                                <span data-src="{if isset($Data.images)}{$Data.images.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" data-media="(min-width: 64em)"></span>
                                <span data-src="{if isset($Data.images)}{$Data.images.5}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" data-media="(min-width: 120em)"></span>

                                <noscript>
                                    <img src="{if isset($Data.images)}{$Data.image.3}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" alt="{$Data.categoryName|strip_tags}">
                                </noscript>
                            </span>
                        {/block}
                    {/if}

                    {* Category teaser title *}
                    {block name="widget_emotion_component_category_teaser_title"}
                        <a class="box--title" href="{if $Data.blog_category}{url controller=blog action=index sCategory=$Data.category_selection}{else}{url controller=cat action=index sCategory=$Data.category_selection}{/if}" title="{$Data.categoryName|strip_tags}">
                            {$Data.categoryName}
                        </a>
                    {/block}
                </a>
            {/block}
        </div>
    {/block}
{/if}