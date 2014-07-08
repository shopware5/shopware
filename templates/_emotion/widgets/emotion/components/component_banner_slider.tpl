
<script type="text/javascript">
(function($) {
    $(document).ready(function() {
        {$sliderHeight = $sElementHeight}
        {if $Data.banner_slider_title}
            {$sliderHeight = $sliderHeight - 36};
        {/if}
        var config  = {
            'title': '{$Data.banner_slider_title}',
            'headline': {if $Data.banner_slider_title}true{else}false{/if},
            'navigation': {if $Data.banner_slider_navigation}true{else}false{/if},
            'scrollSpeed': ~~(1 * '{$Data.banner_slider_scrollspeed}'),
            'rotateSpeed': ~~(1 * '{$Data.banner_slider_rotatespeed}'),
            'rotate': {if $Data.banner_slider_rotation}true{else}false{/if},
            'layout': 'horizontal',
            'showNumbers': {if $Data.banner_slider_numbers}true{else}false{/if},
            'navigation': {if $Data.banner_slider_arrows}true{else}false{/if},
            'showArrows': {if $Data.banner_slider_arrows}true{else}false{/if},
            'scrollWidth': ~~(1 * '{$sElementWidth}'),
            'scrollHeight': ~~(1 * '{$sElementHeight}')
        };

        var slider = $('.slider_banner_{$Data.objectId}').ajaxSlider('locale', config);
        slider.find('.sliding_outer, .sliding_container').css('height', {$sliderHeight});
        slider.find('.ajaxSlider').css('height', {$sElementHeight});
    });

})(jQuery);
</script>
<div class="slider_banner_{$Data.objectId} banner-slider-emotion" style="height:{$sElementHeight}px">
    {foreach $Data.values as $banner}
        <div class="slide" style="width:{$sElementWidth}px; height: {$sliderHeight}px">
            {if $banner.link}
                <a href="{$banner.link}">
                    <img src="{$banner.path}" alt="{$banner.altText}" {if $banner.title}title="{$banner.title}" {/if}/>
                </a>
            {else}
                <img src="{$banner.path}" alt="{$banner.altText}" {if $banner.title}title="{$banner.title}" {/if}/>
            {/if}

        </div>
    {/foreach}
</div>
