{extends file='parent:frontend/listing/box_similar.tpl'}

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
    {if $sArticle.pseudoprice}
    	<span class="pseudo">{s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$sArticle.pseudoprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</span>
    {/if}
    <span class="price{if $sArticle.pseudoprice} pseudo_price{/if}">{$sArticle.price|currency} *</span>
</p>
{/block}