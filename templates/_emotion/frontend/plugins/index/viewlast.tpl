{* Last seen articles *}
<div class="viewlast">
	<h2 class="heading">{s name='WidgetsRecentlyViewedHeadline'}{/s}</h2>
	<ul></ul>
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