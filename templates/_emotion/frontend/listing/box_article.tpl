{extends file='parent:frontend/listing/box_article.tpl'}

{* New *}
{block name='frontend_listing_box_article_new'}
	{if $sArticle.newArticle}
	<div class="ico_new" {if $sArticle.pseudoprice}style="top:50px;"{/if}>{se name='ListingBoxNew'}{/se}</div>
	{/if}
{/block}

{* Description *}
{block name='frontend_listing_box_article_description'}
	{if $sTemplate eq 'listing-1col'}
		{assign var=size value=270}
	{else}
		{assign var=size value=60}
	{/if}
	<p class="desc">
		{if $sTemplate}
			{$sArticle.description_long|strip_tags|truncate:$size}
		{/if}
	</p>
{/block}

{* Unit price *}
{block name='frontend_listing_box_article_unit'}
{if $sArticle.purchaseunit}
    <div class="{if !$sArticle.pseudoprice}article_price_unit{else}article_price_unit_pseudo{/if}">
        {if $sArticle.purchaseunit && $sArticle.purchaseunit != 0}
            <p>
            	<span class="purchaseunit">
                	<strong>{se name="ListingBoxArticleContent"}{/se}:</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
                </span>
        {/if}
        {if $sArticle.purchaseunit != $sArticle.referenceunit}
                {if $sArticle.referenceunit}
                	<span class="referenceunit">
                     ({$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$sArticle.referenceunit} {$sArticle.sUnit.description})
                    </span>
                {/if}
            </p>
        {/if}
    </div>
{/if}
{/block}    	

{* Article Price *}
{block name='frontend_listing_box_article_price'}
<p class="{if $sArticle.pseudoprice}pseudoprice{else}price{/if}{if $sArticle.purchaseunit != $sArticle.referenceunit && !$sArticle.pseudoprice} both{/if}">
    {if $sArticle.pseudoprice}
    	<span class="pseudo">{s name="reducedPrice"}Statt: {/s}{$sArticle.pseudoprice|currency} {s name="Star"}*{/s}</span>
    {/if}
    <span class="price">{if $sArticle.priceStartingFrom && !$sArticle.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$sArticle.price|currency} {s name="Star"}*{/s}</span>
</p>
{/block}

{block name='frontend_listing_box_article_actions' append}
	{if $sArticle.pseudoprice}
		<div class="pseudo_percent">%</div>
	{/if}
{/block}