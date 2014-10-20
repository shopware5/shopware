{extends file="frontend/listing/product-box/box-basic.tpl"}

{block name="frontend_listing_box_article_rating"}{/block}

{block name="frontend_listing_box_article_description"}{/block}

{block name="frontend_listing_box_article_actions"}{/block}

{* Unit price label *}
{block name='frontend_listing_box_article_unit_label'}
    <span class="price--label label--purchase-unit is--nowrap">
        {s namespace="frontend/listing/box_article" name="ListingBoxArticleContent"}{/s}
    </span>
{/block}