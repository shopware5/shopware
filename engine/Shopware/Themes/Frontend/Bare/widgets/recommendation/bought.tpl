{if $boughtArticles}
    <div class="bought--products panel">
		<div class="bought--content panel--body" data-product-slider="true">
			<div class="product-slider--container">
				{foreach $boughtArticles as $article}
					{include file="widgets/recommendation/item.tpl" article=$article}
				{/foreach}
			</div>
		</div>
    </div>
{/if}