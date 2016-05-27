{* Slider panel *}
{block name="widget_emotion_component_product_slider"}
    <div class="emotion--product-slider panel{if !$Data.no_border} has--border{/if}">

        {* Title *}
        {block name="widget_emotion_component_product_slider_title"}
            {if $Data.article_slider_title}
                <div class="panel--title is--underline product-slider--title">
                    {$Data.article_slider_title}
                </div>
            {/if}
        {/block}

        {* Slider content based on the configuration *}
        {block name="widget_emotion_component_product_slider_content"}

            <div class="product-slider--content"
                 data-product-slider="true"
                 data-itemsPerPage="{$itemCols}"
                 {if $Data.article_slider_type !== 'selected_article'}data-mode="ajax"{/if}
                 {if $Data.ajaxFeed}data-ajaxCtrlUrl="{$Data.ajaxFeed}"{/if}
                 {if $Data.article_slider_category}data-ajaxCategoryID="{$Data.article_slider_category}"{/if}
                 {if $Data.article_slider_max_number}data-ajaxMaxShow="{$Data.article_slider_max_number}"{/if}
                 {if $Data.article_slider_arrows != 1}data-arrowControls="false"{/if}
                 {if $Data.article_slider_scrollspeed}data-animationSpeed="{$Data.article_slider_scrollspeed}"{/if}
                 {if $Data.article_slider_rotatespeed}data-autoSlideSpeed="{$Data.article_slider_rotatespeed / 1000}"{/if}
                 {if $Data.article_slider_rotation}data-autoSlide="true"{/if}>

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
