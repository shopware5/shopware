
<script type="text/javascript">
(function($) {
    $(document).ready(function() {
        {$sliderHeight = $sElementHeight}
        {if $Data.manufacturer_slider_title}
            {$sliderHeight = $sliderHeight - 36};
        {/if}
        var config  = {
            'title': '{$Data.manufacturer_slider_title}',
            'headline': {if $Data.manufacturer_slider_title}true{else}false{/if},
            'navigation': {if $Data.manufacturer_slider_navigation}true{else}false{/if},
            'scrollSpeed': ~~(1 * '{$Data.manufacturer_slider_scrollspeed}'),
            'rotateSpeed': ~~(1 * '{$Data.manufacturer_slider_rotatespeed}'),
            'rotate': {if $Data.manufacturer_slider_rotation}true{else}false{/if},
            'layout': 'horizontal',
            'showNumbers': {if $Data.manufacturer_slider_numbers}true{else}false{/if},
            'navigation': false,
            'showArrows': {if $Data.manufacturer_slider_arrows}true{else}false{/if},
            'scrollWidth': ~~(1 * '{$sElementWidth}'),
            'scrollHeight': ~~(1 * '{$sElementHeight}')
        };

        var slider = $('.slider_manufacturer_{$Data.objectId}').ajaxSlider('locale', config);
        slider.find('.sliding_outer, .sliding_container').css('height', {$sliderHeight});
        slider.find('.ajaxSlider').css('height', {$sElementHeight-2});
        slider.find('.slide').css({
            'width': {$sElementWidth},
            'height': {$sliderHeight}
        });
    });
})(jQuery);
</script>
<div class="slider_manufacturer_{$Data.objectId} slider-manufacturer" style="height:{$sElementHeight}px">

	{if $colWidth eq 3}
		{$perPage = $sColWidth + 1}
	{elseif $colWidth eq 2}
		{$perPage = $sColWidth + 1}
	{else}
		{$perPage = $sColWidth}
	{/if}
	
    {foreach $Data.values|array_chunk:$perPage as $suppliers}
        <div class="slide" style="width:{$sElementWidth}px">
            <div class="inner-slide">
            {foreach $suppliers as $supplier}
                <div class="supplier">
                    <a href="{$supplier.link}" title="{$supplier.name}" class="image-wrapper{if !$supplier.image} text{/if}">
                    {if $supplier.image}
                            <span class="vertical-center"></span>
                            <img src="{$supplier.image}" alt="{$supplier.name}" />
                    {else}
                        {$supplier.name}
                    {/if}
                    </a>
                </div>
            {/foreach}
            <div class="clear"></div>
            </div>
        </div>
    {/foreach}
</div>
