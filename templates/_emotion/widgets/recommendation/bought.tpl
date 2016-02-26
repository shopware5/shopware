{if $boughtArticles}
    <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                $('.bought-slider').ajaxSlider('locale', {
                    'height': 130,
                    'width': 896,
                    'scrollWidth': 896,
                    'title': '{s name="DetailBoughtArticlesSlider" namespace="frontend/plugins/recommendation/blocks_detail"}Kunden kauften auch:{/s}',
                    'titleClass': 'headingbox_nobg',
                    'headline': true,
                    'navigation': false,
                    'showNumbers': false,
                    'containerCSS': {
                        'marginBottom': '20px'
                    }
                });
            });
        })(jQuery);
    </script>
    <div class="bought-slider">
        {foreach $boughtArticles|array_chunk:$perPage as $articles}
            {include file="widgets/recommendation/slide_articles.tpl" articles=$articles}
        {/foreach}
    </div>
{/if}
