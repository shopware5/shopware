{namespace name="frontend/listing/box_article"}

{$tooltip = "{s name='ListingBoxArticleContent'}{/s}"|escape:'html'}
{$hasPurchaseUnit = $sArticle.purchaseunit && $sArticle.purchaseunit != 0}
{$hasReferenceUnit = $sArticle.purchaseunit && $sArticle.referenceunit && $sArticle.purchaseunit != $sArticle.referenceunit}

{if $hasPurchaseUnit}
    {$purchaseUnit = "{$sArticle.purchaseunit} {$sArticle.sUnit.description}"}
    {$tooltip = "{$tooltip} {$purchaseUnit|escape:'html'}"}
{/if}

{if $hasReferenceUnit}
    {$price = "{$sArticle.referenceprice|currency}"}
    {$unit = "{s name='Star'}{/s} / {$sArticle.referenceunit} {$sArticle.sUnit.description}"}
    {$referenceUnit = "({$price} {$unit|escape:'html'})"}
    {$tooltip = "{$tooltip} {$referenceUnit}"}
{/if}

<div class="price--unit" title="{$tooltip}">

    {* Price is based on the purchase unit *}
    {if $hasPurchaseUnit}

        {* Unit price label *}
        {block name='frontend_listing_box_article_unit_label'}
            <span class="price--label label--purchase-unit is--bold is--nowrap">
                {s name="ListingBoxArticleContent"}{/s}
            </span>
        {/block}

        {* Unit price content *}
        {block name='frontend_listing_box_article_unit_content'}
            <span class="is--nowrap">
                {$purchaseUnit}
            </span>
        {/block}
    {/if}

    {* Unit price is based on a reference unit *}
    {if $hasReferenceUnit}

        {* Reference unit price content *}
        {block name='frontend_listing_box_article_unit_reference_content'}
            <span class="is--nowrap">
                {$referenceUnit}
            </span>
        {/block}
    {/if}
</div>
