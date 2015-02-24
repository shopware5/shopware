{block name="frontend_widgets_banner_slider"}
    <div class="emotion--banner-slider image-slider"
         data-image-slider="true"
         data-thumbnails="false"
         data-lightbox="false"
         data-loopSlides="true"
         data-animationSpeed="{$Data.banner_slider_scrollspeed}"
         data-arrowControls="{if $Data.banner_slider_arrows}true{else}false{/if}"
         data-autoSlideInterval="{$Data.banner_slider_rotatespeed}"
         data-autoSlide="{if $Data.banner_slider_rotation}true{else}false{/if}"
         data-imageSelector=".image-slider--item">

        {if $Data.banner_slider_title}
            <div class="banner-slider--title">{$Data.banner_slider_title}</div>
        {/if}

        {block name="frontend_widgets_banner_slider_container"}
            <div class="banner-slider--container image-slider--container">
                {block name="frontend_widgets_banner_slider_slide"}
                    <div class="banner-slider--slide image-slider--slide">
                        {foreach $Data.values as $banner}
                            {strip}
                            <style type="text/css">
                                {if empty($banner.thumbnails)}
                                    #banner--{$Data.objectId}-{$banner@index} {
                                        background-image: url('{$banner.source}');
                                    }
                                {else}
                                    {$images = $banner.thumbnails}
                                
                                    #banner--{$Data.objectId}-{$banner@index} {
                                        background-image: url('{$images[0].source}');
                                    }

                                    {if isset($images[0].retinaSource)}
                                    @media screen and (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
                                        #banner--{$Data.objectId}-{$banner@index} {
                                            background-image: url('{$images[0].retinaSource}');
                                        }
                                    }
                                    {/if}

                                    @media screen and (min-width: 48em) {
                                        #banner--{$Data.objectId}-{$banner@index} {
                                            background-image: url('{$images[1].source}');
                                        }
                                    }

                                    {if isset($images[1].retinaSource)}
                                    @media screen and (min-width: 48em) and (-webkit-min-device-pixel-ratio: 2),
                                           screen and (min-width: 48em) and (min-resolution: 192dpi) {
                                        #banner--{$Data.objectId}-{$banner@index} {
                                            background-image: url('{$images[1].retinaSource}');
                                        }
                                    }
                                    {/if}

                                    @media screen and (min-width: 78.75em) {
                                        .is--fullscreen #banner--{$Data.objectId}-{$banner@index} {
                                            background-image: url('{$images[2].source}');
                                        }
                                    }

                                    {if isset($images[2].retinaSource)}
                                    @media screen and (min-width: 78.75em) and (-webkit-min-device-pixel-ratio: 2),
                                           screen and (min-width: 78.75em) and (min-resolution: 192dpi) {
                                        .is--fullscreen #banner--{$Data.objectId}-{$banner@index} {
                                            background-image: url('{$images[2].retinaSource}');
                                        }
                                    }
                                    {/if}
                                {/if}
                            </style>
                            {/strip}

                            {block name="frontend_widgets_banner_slider_item"}
                                <div class="banner-slider--item image-slider--item" id="banner--{$Data.objectId}-{$banner@index}">
                                    {if $banner.link}
                                        {block name="frontend_widgets_banner_slider_link"}
                                            <a class="banner-slider--link" href="{$banner.link}" title="{$banner.title|escape}">
                                                {$banner.altText}
                                            </a>
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