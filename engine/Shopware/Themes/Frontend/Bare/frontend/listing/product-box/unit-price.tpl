{namespace name="frontend/listing/box_article"}

{if $sArticle.purchaseunit}
    <div class="product--price price--unit">

        {* Price is based on the purchase unit *}
        {if $sArticle.purchaseunit && $sArticle.purchaseunit != 0}

            {* Unit price label *}
            {block name='frontend_listing_box_article_unit_label'}
                <strong class="price--label label--purchase-unit">
                    {s name="ListingBoxArticleContent"}{/s}
                </strong>
            {/block}

            {* Unit price content *}
            {block name='frontend_listing_box_article_unit_content'}
                {$sArticle.purchaseunit} {$sArticle.sUnit.description}
            {/block}
        {/if}

        {* Unit price is based on a reference unit *}
        {if $sArticle.purchaseunit && $sArticle.purchaseunit != $sArticle.referenceunit}

            {* Reference unit price label *}
            {block name='frontend_listing_box_article_unit_reference_label'}
                <strong class="price--label label--reference-unit">
                    {s name="ListingBoxArticleContent"}{/s}
                </strong>
            {/block}

            {* Reference unit price content *}
            {block name='frontend_listing_box_article_unit_reference_content'}
                ({$sArticle.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                / {$sArticle.referenceunit} {$sArticle.sUnit.description})
            {/block}
        {/if}
    </div>
{/if}