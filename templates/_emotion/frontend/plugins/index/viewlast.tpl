{* Last seen articles *}
<div class="viewlast">
	<h2 class="heading">{s name='WidgetsRecentlyViewedHeadline'}{/s}</h2>
	<ul></ul>
</div>
<script>
	;(function($, window, document) {
		$(document).ready(function() {
			var shopId = '{$Shop->getId()}',
				basePath = '{$Shop->getBaseUrl()}',
				localStorage = $.isLocalStorageSupported ? window.localStorage : new StoragePolyFill('local');

			if(localStorage.getItem('lastSeenArticleIndex-' + shopId + '-' + basePath)) {
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
		});
	}(jQuery, window, document));
</script>