<div class="grid_3 compare_article">

	{* Picture *}
	<div class="picture">
		{block name="frontend_compare_article_picture"}
		<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">
			{if $sArticle.image.src}
				<img src="{$sArticle.image.src.2}" alt="{$sArticle.articleName}" />
			{else}
				<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{$sArticle.articleName}" />
			{/if}
		</a>
		{/block}
	</div>

	{* Name *}
	<div class="name">
		{block name='frontend_compare_article_name'}
			<h3><a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">{$sArticle.articleName|truncate:47}</a></h3>

			{* More informations button *}
			<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}" class="button-right small_right">
				{s name='ListingBoxLinkDetails' namespace="frontend/listing/box_article"}{/s}
			</a>
		{/block}
	</div>

	{* User Votings *}
	<div class="votes">
		{block name='frontend_compare_votings'}
		<div class="star star{$sArticle.sVoteAverange.averange|round}">Star Rating</div>
		{/block}
	</div>

	{* Description *}
	<div class="desc">
		{block name='frontend_compare_description'}
		<p>
    		{$sArticle.description_long|truncate:150}
    	</p>
    	{/block}
	</div>

	{* Price *}
	<div class="price">
		{block name='frontend_compare_price'}
		<p {if $sArticle.has_pseudoprice} class="article-price2" {else} class="article-price"{/if}>
    		{if $sArticle.has_pseudoprice}<s>{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$sArticle.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</s><br />{/if}
    		<strong>{if $sArticle.priceStartingFrom}ab {/if}{$sArticle.price|currency}</strong>*
    	</p>
    	{/block}

    	{block name='frontend_compare_unitprice'}
    	{if $sArticle.purchaseunit}
            <div class="article_price_unit">
                <p>
                    <strong>{se name="CompareContent"}{/se}:</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
                </p>
                {if $sArticle.purchaseunit != $sArticle.referenceunit}
                    <p>
                        {if $sArticle.referenceunit}
                            <strong class="baseprice">{se name="CompareBaseprice"}{/se}:</strong> {$sArticle.referenceunit} {$sArticle.sUnit.description} = {$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                        {/if}
                    </p>
                {/if}
            </div>
        {/if}
        {/block}
	</div>

	{* Properties *}
	{foreach from=$sArticle.sProperties item=property}
		{block name='frontend_compare_properties'}
			<div class="property" style="background-color:#fff;">
				{if $property.value}{$property.value}{else}-{/if}
			</div>
		{/block}
	{/foreach}
</div>
