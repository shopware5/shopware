
{if $viewedArticles}
    <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                $('.viewed-slider').ajaxSlider('locale', {
                    'height': 130,
                    'width': 896,
                    'scrollWidth': 896,
                    'title': '{s name="DetailViewedArticlesSlider" namespace="frontend/plugins/recommendation/blocks_detail"}Kunden haben sich ebenfalls angesehen:{/s}',
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
    <div class="viewed-slider">
        {foreach $viewedArticles|array_chunk:$perPage as $articles}
            {include file="widgets/recommendation/slide_articles.tpl" articles=$articles}
        {/foreach}
    </div>
{/if}
