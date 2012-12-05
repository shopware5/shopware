<div class="slide">
	{foreach from=$sSliderArticles item=article}

		{assign var=image value=$article.image.src.2}
		<div class="article_box">
		{if $image}
			<a style="background: url({$image}) no-repeat scroll center center transparent;" class="artbox_thumb" title="{$article.articleName}" href="{$article.linkDetails}">
			</a>
		{else}
			<a class="artbox_thumb no_picture" title="{$article.articleName}" href="{$article.linkDetails}">
			</a>
		{/if}
		<a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:35}</a>
		<p class="price">
			{s namespace="frontend/bonus_system/recommendation" name="ForXBonusPoints"}f&uuml;r <strong>{$article.required_points} Punkte</strong>{/s}
		</p>
		</div>
	{/foreach}
</div>
<div class="pages">{$sSliderPages}</div>
