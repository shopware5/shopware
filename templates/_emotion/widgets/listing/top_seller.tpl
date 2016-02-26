
<script type="text/javascript">
(function($) {
    $(document).ready(function() {
        $('.topseller-slider').ajaxSlider('locale', {
            'height': 210,
            'layout': 'horizontal',
            'scrollWidth': 796,
            'title': '{s name="TopsellerHeading" namespace=frontend/plugins/index/topseller}{/s}',
            'titleClass': 'headingbox_nobg',
            'headline': true,
            'navigation': true,
            'showNumbers': false,
            'containerCSS': {
                'marginTop': '20px',
                'marginBottom': '20px'
            }
        });
    });
})(jQuery);
</script>	

{if $sCharts|@count}
<div class="topseller-slider">
    {foreach from=$sCharts item=article}
        {if $article@index % $perPage == 0}
            <div class="slide">
        {/if}
                
        {assign var=image value=$article.image.src.2}
        <div class="article_box{cycle values=",,, noborder"}">
	        <span class="numbers">{$article@index + 1}</span>
        {if $image}
			<a style="background: url({$image}) no-repeat scroll center center transparent;" class="artbox_thumb" title="{$article.articleName|escape}" href="{$article.linkDetails}"></a>
        {else}
			<a class="artbox_thumb no_picture" title="{$article.articleName|escape}" href="{$article.linkDetails}"></a>
        {/if}
			<a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:28}</a>
        </div>

        {if $article@index % $perPage == ($perPage - 1) || $article@last}
        </div>
        {/if}
    {/foreach}
</div>
<div class="space">&nbsp;</div>
{/if}
