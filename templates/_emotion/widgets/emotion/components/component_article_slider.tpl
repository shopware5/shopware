
<script type="text/javascript">
(function($) {
    $(document).ready(function() {
        {$sliderHeight = $sElementHeight}
        {if $Data.article_slider_title}
            {$sliderHeight = $sliderHeight - 36};
        {/if}
        {$perPage = $sColWidth }
        var config  = {
            'url': '{$Data.ajaxFeed}',
            'title': "{$Data.article_slider_title}",
            'headline': {if $Data.article_slider_title}true{else}false{/if},
            'scrollSpeed': ~~(1 * '{$Data.article_slider_scrollspeed}'),
            'rotateSpeed': ~~(1 * '{$Data.article_slider_rotatespeed}'),
            'rotate': {if $Data.article_slider_rotation}true{else}false{/if},
            'layout': 'horizontal',
            'showNumbers': {if $Data.article_slider_numbers}true{else}false{/if},
            'navigation': {if $Data.article_slider_type == 'selected_article'}true{else}false{/if},
            'showArrows': {if $Data.article_slider_arrows}true{else}false{/if},
            'scrollWidth': ~~(1 * '{$sElementWidth}'),
            'scrollHeight': ~~(1 * '{$sElementHeight}'),
            'skipInitalRendering': true,
            'maxPages': ~~(1 * '{$Data.pages}'),
            'extraParams': {
                'category': ~~(1 * '{$Data.categoryId}'),
                'start': 0,
                'limit': ~~(1 * '{$perPage}'),
                'elementWidth': ~~(1 * '{$sElementWidth}'),
                'elementHeight': ~~(1 * '{$sliderHeight-5}'),
                'max': ~~(1 * '{$Data.article_slider_max_number}')
            }
        };

        var slider = $('.slider_article_{$Data.objectId}').ajaxSlider({if $Data.article_slider_type == 'selected_article'}'locale'{else}'ajax'{/if}, config);
        slider.find('.sliding_outer, .sliding_container').css('height', {$sliderHeight});
        slider.find('.ajaxSlider').css('height', {$sElementHeight-2});
    });

})(jQuery);
</script>
<div class="slider_article_{$Data.objectId} article-slider-emotion" style="height:{$sElementHeight}px">
{if $Data.article_slider_type == 'selected_article'}
    {foreach $Data.values|array_chunk:$perPage as $articles}
        {include file="widgets/emotion/slide_articles.tpl" articles=$articles sElementWidth=$sElementWidth sPerPage=$perPage sElementHeight=$sliderHeight-5}
    {/foreach}
{/if}
</div>
