{* Last seen articles *}
<div class="viewlast">
	<h2 class="heading">{s name='WidgetsRecentlyViewedHeadline'}{/s}</h2>
	<ul></ul>
</div>
<script>
    jQuery(function($) {
        var NumberOfArticles = '{$sLastArticlesNum}';
        $('.viewlast').lastSeenArticlesDisplayer(NumberOfArticles);
    })
</script>