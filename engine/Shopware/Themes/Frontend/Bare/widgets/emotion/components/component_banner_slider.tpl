{$dataAllConfig = "{ thumbnails: false, leftArrowCls: 'product-slider--arrow is--left', rightArrowCls: 'product-slider--arrow is--right', lightbox: false, animationSpeed: {$Data.banner_slider_scrollspeed}, arrowControls: {if $Data.banner_slider_arrows}true{else}false{/if}, autoSlideInterval: {$Data.banner_slider_rotatespeed}, autoSlide: {if $Data.banner_slider_rotation}true{else}false{/if} }"}

{block name="frontend_widgets_banner_slider"}
    <div class="image-slider" data-all="imageSlider" data-xs-config="{$dataAllConfig}" data-m-config="{$dataAllConfig}" data-l-config="{$dataAllConfig}" data-xl-config="{$dataAllConfig}">

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