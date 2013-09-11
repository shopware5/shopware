{* Last seen articles *}
<div class="viewlast">
	<h2 class="heading">{s name='WidgetsRecentlyViewedHeadline'}{/s}</h2>
	<ul></ul>
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