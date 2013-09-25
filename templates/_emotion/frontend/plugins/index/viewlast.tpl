{* Last seen articles *}
<div class="viewlast">
	<h2 class="heading">{s name='WidgetsRecentlyViewedHeadline'}{/s}</h2>
	<ul></ul>
</div>
<script>
    jQuery(function($) {
        var shopId = '{$Shop->getId()}';
        var basePath = '{$Shop->getBaseUrl()}';
        var savedArticleCount = localStorage.getItem('lastSeenArticleIndex-' + shopId + '-' + basePath);
        if(savedArticleCount) {
            var numberOfArticles = '{config name=lastarticlestoshow}';
            
            $('.viewlast').lastSeenArticlesDisplayer({
                numArticles: numberOfArticles,
                shopId: shopId,
                basePath: basePath
            });
        }
        else {
            $('.viewlast').hide();
        }
    })
</script>