{* Last seen articles *}
<div class="viewlast">
	<p class="heading">{s name='WidgetsRecentlyViewedHeadlineFixed'}Zuletzt Angeschaut{/s}</p>
</div>
<script>
    jQuery(function($) {
        var anzsavedarticles = localStorage.getItem('lastSeenArticleIndex');
        if(anzsavedarticles) {
            var NumberOfArticles = '{config name=lastarticlestoshow}';
            $('.viewlast').lastSeenArticlesDisplayer(NumberOfArticles);
        }
        else {
            $('.viewlast').hide();
        }
    })
</script>