{* Last seen articles *}
<div class="viewlast">
	<p class="heading">{s name='WidgetsRecentlyViewedHeadline'}{/s}</p>
</div>
<script>
	(function($, window, document) {
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
	}(jQuery,  window, document));
</script>