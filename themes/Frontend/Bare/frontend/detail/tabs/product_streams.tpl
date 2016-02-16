{* Product-Streams slider *}
{block name='frontend_detail_index_streams_slider'}
    <div class="product-streams--content">
        <div class="product-slider"
             data-product-slider="true"
             data-mode="ajax"
             data-initOnEvent="onShowContent-productStreamSliderId-{$relatedProductStream.id}"
             data-ajaxCtrlUrl="{url module=widgets controller=emotion action=productStreamArticleSlider streamId=$relatedProductStream.id productBoxLayout="slider"}"
             data-ajaxMaxShow="40">

            {* Product-Streams slider container *}
            {block name='frontend_detail_index_streams_slider_container'}
                <div class="product-slider--container">
                </div>
            {/block}
        </div>
    </div>
{/block}