
{$width = $sElementWidth-61}
<div class="slide" style="width:{$width|round:0}px;height:{$sElementHeight}px">
	{foreach from=$articles item=article}
		{assign var=image value=$article.image.src.4}
        <div class="outer-article-box" style="width:{'100' / $sPerPage}%">
            <div class="article_box">
            <!-- article 1 -->
            {if $image}
            <a class="article-thumb-wrapper" title="{$article.articleName}" href="{$article.linkDetails}">
                <img src="{$image}" title="{$article.articleName}" />
            </a>
            {else}
            <a class="article-thumb-wrapper" title="{$article.articleName}" href="{$article.linkDetails}">
                <img src="{link file="frontend/_resources/images/no_picture.jpg"}" title="{$article.articleName}" />
            </a>
            {/if}
            <a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:35}</a>

            {if $article.purchaseunit && $article.purchaseunit != 0}
                <p class="article-purchase-unit">
                    <span class="purchaseunit">
                        <strong>{se name="ListingBoxArticleContent" namespace="frontend/listing/box_article"}{/se}:</strong> {$article.purchaseunit} {$article.sUnit.description}
                    </span>
            {/if}
            {if $article.purchaseunit != $article.referenceunit}
                    {if $article.referenceunit}
                        <span class="referenceunit">
                         ({$article.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$article.referenceunit} {$article.sUnit.description})
                        </span>
                    {/if}
                </p>
            {/if}

            <p class="price">
                {if $article.has_pseudoprice}
                    <span class="pseudo">
                    <em>
                    	{s name="reducedPrice"}Statt:{/s} {$article.pseudoprice|currency} {s name="Star"}*{/s}
                    </em>
                    </span>
                {/if}
                <span class="price{if $article.has_pseudoprice} pseudo{/if}">{if $article.priceStartingFrom && !$article.liveshoppingData}{s namespace="frontend/plugins/recommendation/slide_articles" name='ListingBoxArticleStartsAt'}{/s} {/if}{$article.price|currency} *</span>
            </p>
            </div>
        </div>
	{/foreach}
</div>
{if $pages}
<div class="pages">{$pages}</div>
{/if}
