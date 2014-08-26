{$dataXsConfig="{ perPage: 1, perSlide: 1, touchControl: true, ajaxMaxShow: {$Data.article_slider_max_number}, controllerUrl: '{$Data.ajaxFeed}', mode: '{if $Data.article_slider_type == 'selected_article'}local{else}ajax{/if}', categoryID: {$sCategoryId} }"}
{$dataMConfig="{ perPage: 3, perSlide: 1, touchControl: true, ajaxMaxShow: {$Data.article_slider_max_number}, controllerUrl: '{$Data.ajaxFeed}', mode: '{if $Data.article_slider_type == 'selected_article'}local{else}ajax{/if}', categoryID: {$sCategoryId} }"}
{$dataLConfig="{ perPage: 3, perSlide: 1, touchControl: true, ajaxMaxShow: {$Data.article_slider_max_number}, controllerUrl: '{$Data.ajaxFeed}', mode: '{if $Data.article_slider_type == 'selected_article'}local{else}ajax{/if}', categoryID: {$sCategoryId} }"}
{$dataXlConfig="{ perPage: 4, perSlide: 4, ajaxMaxShow: {$Data.article_slider_max_number}, controllerUrl: '{$Data.ajaxFeed}', mode: '{if $Data.article_slider_type == 'selected_article'}local{else}ajax{/if}', categoryID: {$sCategoryId} }"}

{* Slider panel *}
{block name="widget_emotion_component_product_slider"}
    <div class="panel has--border">

        {* Title *}
        {block name="widget_emotion_component_product_slider_title"}
            {if $Data.article_slider_title}
                <div class="panel--title article-slider--title is--underline">{$Data.article_slider_title}</div>
            {/if}
        {/block}

        {* Slider content based on the configuration *}
        {block name="widget_emotion_component_product_slider_content"}
            <div class="panel--body is--wide product-slider" data-all="productSlider" data-xs-config="{$dataXsConfig}" data-m-config="{$dataMConfig}" data-xl-config="{$dataXlConfig}">
                <div class="product-slider--container">
                    {if $Data.article_slider_type == 'selected_article'}
                        {$articles = $Data.values}

                        {* Products inside the slider *}
                        {block name="widget_emotion_component_product_slider"}
                            {include file="widgets/emotion/slide_articles.tpl" articles=$articles sElementWidth=$sElementWidth sPerPage=$perPage sElementHeight=$sliderHeight-5}
                        {/block}
                    {/if}
                </div>
            </div>
        {/block}
    </div>
{/block}