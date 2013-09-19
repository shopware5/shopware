{* Last seen articles *}
<div class="viewlast">
	<p class="heading">{s name='WidgetsRecentlyViewedHeadline'}{/s}</p>
</div>
<script>
    jQuery(function($) {
        var shopId = '{$Shop->getId()}';
        var savedArticleCount = localStorage.getItem('lastSeenArticleIndex-' + shopId);
        if(savedArticleCount) {
            var numberOfArticles = '{config name=lastarticlestoshow}';

            $('.viewlast').lastSeenArticlesDisplayer({
                numArticles: numberOfArticles,
                shopId: shopId
            });
        }
        else {
            $('.viewlast').hide();
        }
    })
</script>