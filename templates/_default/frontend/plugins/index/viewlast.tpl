{* Last seen articles *}
<div class="viewlast">
	<p class="heading">{s name='WidgetsRecentlyViewedHeadline'}{/s}</p>
</div>
<script>
    jQuery(function($) {
        var savedArticleCount = localStorage.getItem('lastSeenArticleIndex');
        if(savedArticleCount) {
            var numberOfArticles = '{config name=lastarticlestoshow}';
            $('.viewlast').lastSeenArticlesDisplayer(numberOfArticles);
        }
        else {
            $('.viewlast').hide();
        }
    })
</script>