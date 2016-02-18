{block name="frontend_listing_box_similar"}
<div class="artbox">
	<div class="inner">

		{* Article picture *}
		{block name='frontend_listing_box_similar_article_picture'}
		<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}" class="artbox_thumb {if !$sArticle.image.src}no_picture{/if}" {if $sArticle.image.src}
			style="background: #fff url({$sArticle.image.src.1}) no-repeat center center"{/if}>&nbsp;
		{/block}
		</a>

		<div class="title_price">
			{* Article name *}
			<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}">
				{block name='frontend_listing_box_similar_name'}
				<strong class="title">{$sArticle.articleName|truncate:47}</strong>
				{/block}
			</a>

			{* Unit price *}
			{block name='frontend_listing_similar_article_unit'}
				{if $sArticle.purchaseunit != $sArticle.referenceunit}
				    <div class="article_price_unit">
				        <p>
				        <strong>{se name="ListingBoxArticleContent" namespace="frontend/listing/box_article"}{/se}:</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
				        {if $sArticle.referenceunit}
				            ({$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$sArticle.referenceunit} {$sArticle.sUnit.unit})
				        {/if}
				        </p>
				    </div>
				{/if}
			{/block}

			{* Price *}
			{block name='frontend_listing_box_similar_price'}
				<p class="price">
				    {if $sArticle.has_pseudoprice}
				    	<span class="pseudo">{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$sArticle.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</span>
				    {/if}
				    <span class="price{if $sArticle.has_pseudoprice} pseudo_price{/if}">{$sArticle.price|currency} *</span>
				</p>
	        {/block}
        </div>

       	{* Compare and more *}
       	{block name='frontend_listing_box_similar_actions'}
       	<div class="actions">
			<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{s name='SimilarBoxMore'}{/s} {$sArticle.articleName}" class="more">{se name='SimilarBoxLinkDetails'}{/se}</a>
		</div>
		{/block}

	</div>
</div>
{/block}
