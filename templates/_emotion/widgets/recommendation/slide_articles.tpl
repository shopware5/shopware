
<div class="slide">
	{foreach from=$articles item=article}
		{assign var=image value=$article.image.src.2}
		<div class="article_box">
		<!-- article 1 -->
		{if $image}
		<a style="background: url({$image}) no-repeat scroll center center transparent;" class="artbox_thumb" title="{$article.articleName}" href="{$article.linkDetails}">
		</a>
		{else}
		<a class="artbox_thumb no_picture" title="{$article.articleName}" href="{$article.linkDetails}">
		</a>
		{/if}
		<a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:35}</a>

        {if $article.purchaseunit != $article.referenceunit}
            <div class="article_price_unit">
                <p>
                    <strong>{se name="SlideArticleInfoContent" namespace="frontend/plugins/recommendation/slide_articles"}{/se}:</strong> {$article.purchaseunit} {$article.sUnit.description}
                    {if $article.referenceunit}
                        ({$article.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$article.referenceunit} {$article.sUnit.description})
                    {/if}
                </p>
            </div>
        {/if}

		<p class="price{if $article.purchaseunit}{else} up{/if}">
		<span class="price{if $article.has_pseudoprice} pseudo{/if}">
		{if $article.priceStartingFrom && !$article.liveshoppingData}{s name='ListingBoxArticleStartsAt' namespace="frontend/plugins/recommendation/slide_articles"}{/s} {/if}{$article.price|currency} *</span>
		{if $article.has_pseudoprice}
        	<em>{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$article.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</em>
		{/if}
		</p>
		</div>
	{/foreach}
</div>
<div class="pages">{$pages}</div>
