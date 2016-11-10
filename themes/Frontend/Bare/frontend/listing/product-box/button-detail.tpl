{block name="frontend_listing_product_box_button_detail"}

    {block name="frontend_listing_product_box_button_detail_url"}
        {$url = {$sArticle.linkDetails|rewrite:$sArticle.articleName} }
    {/block}

    {block name="frontend_listing_product_box_button_detail_title"}
        {$title = {$sArticle.articleName|escapeHtml} }
    {/block}

    {block name="frontend_listing_product_box_button_detail_label"}
        {$label = "{s name="ListingBoxLinkDetails" namespace="frontend/listing/box_article"}See details{/s}"}
    {/block}

    {block name="frontend_listing_product_box_button_detail_container"}
        <div class="product--detail-btn">

            {block name="frontend_listing_product_box_button_detail_anchor"}
                <a href="{$url}" class="buybox--button block btn is--icon-right is--center is--large" title="{$label} - {$title}">
                    {block name="frontend_listing_product_box_button_detail_text"}
                        {$label} <i class="icon--arrow-right"></i>
                    {/block}
                </a>
            {/block}
        </div>
    {/block}
{/block}