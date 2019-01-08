{extends file="parent:frontend/listing/product-box/product-price-unit.tpl"}
{namespace name="frontend/listing/box_article"}

{block name="frontend_listing_box_article_unit_label"}
    <span class="price--label label--purchase-unit">
        {s name="DetailDataInfoContent" namespace="frontend/detail/data"}{/s}
    </span>
{/block}