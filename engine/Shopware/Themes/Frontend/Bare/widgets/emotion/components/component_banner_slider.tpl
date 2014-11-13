{block name="frontend_widgets_banner_slider"}
    <div class="image-slider"
         data-image-slider="true"
         data-thumbnails="false"
         data-leftArrowCls="product-slider--arrow is--left"
         data-rightArrowCls="product-slider--arrow is--right"
         data-lightbox="false"
         data-animationSpeed="{$Data.banner_slider_scrollspeed}"
         data-arrowControls="{if $Data.banner_slider_arrows}true{else}false{/if}"
         data-autoSlideInterval="{$Data.banner_slider_rotatespeed}"
         data-autoSlide="{if $Data.banner_slider_rotation}true{else}false{/if}">

        {if $Data.banner_slider_title}
            <div class="image-slider--title">{$Data.banner_slider_title}</div>
        {/if}

        {block name="frontend_widgets_banner_slider_container"}
            <div class="image-slider--container">

                {block name="frontend_widgets_banner_slider_slide"}
                    <div class="image-slider--slide">
                        {foreach $Data.values as $banner}

                            {block name="frontend_widgets_banner_slider_item"}
                                <div class="image-slider--item" style="background-image: url({link file=$banner.path})">
                                    {if $banner.link}
                                        {block name="frontend_widgets_banner_slider_link"}
                                            <a class="image-slider--link" href="{$banner.link}" title="{$banner.title|escape:'html'}"></a>
                                        {/block}
                                    {/if}
                                </div>
                            {/block}
                        {/foreach}
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}