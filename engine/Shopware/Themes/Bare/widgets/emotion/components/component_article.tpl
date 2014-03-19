{extends file='parent:frontend/listing/box_article.tpl'}

{* Disable the "buy now" button *}
{block name='frontend_listing_box_article_actions_buy_now'}{/block}

{* Increase the size of the description text *}
{block name='frontend_listing_box_article_description'}{/block}

{* Unit price *}
{block name='frontend_listing_box_article_unit'}
{if $sArticle.purchaseunit}
    <div class="{if !$sArticle.pseudoprice}article_price_unit{else}article_price_unit_pseudo{/if}">
        {if $sArticle.purchaseunit && $sArticle.purchaseunit != 0}
            <p>
            	<span class="purchaseunit">
                	<strong>{se name="ListingBoxArticleContent" namespace="frontend/listing/box_article"}{/se}:</strong> {$sArticle.purchaseunit} {$sArticle.sUnit.description}
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
