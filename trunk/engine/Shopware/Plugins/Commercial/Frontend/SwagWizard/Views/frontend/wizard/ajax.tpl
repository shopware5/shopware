<div class="slide">
	{foreach from=$WizardArticles item=article}
		{assign var=image value=$article.image.src.2}
		<div class="article_box">
			{if $image}
				<a style="background: url({$image}) no-repeat scroll center center transparent;" class="artbox_thumb" title="{$article.articleName}" href="{$article.linkDetails}"></a>
			{else}
				<a class="artbox_thumb no_picture" title="{$article.articleName}" href="{$article.linkDetails}"></a>
			{/if}
			<a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:35}</a>
			<p class="price">
				<span class="price">{if $article.priceStartingFrom}{s namespace="frontend/checkout/ajax_add_article" name='ListingBoxArticleStartsAt'}{/s} {/if}{$article.price|currency} *</span>
			</p>
		</div>
	{/foreach}
</div>
<div class="pages">{$WizardPages}</div>
