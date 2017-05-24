{namespace name="frontend/checkout/cart_item"}

<div class="table--tr block-group row--discount{if $isLast} is--last-row{/if}">

    {* Product information column *}
    {block name='frontend_checkout_cart_item_discount_name'}
        <div class="table--column column--product block">

            {* Badge *}
            {block name='frontend_checkout_cart_item_discount_badge'}
                <div class="panel--td column--image">
                    <div class="table--media">
                        <div class="basket--badge">
                            {if $lineItem.price.totalPrice >= 0}
                                <i class="icon--arrow-right"></i>
                            {else}
                                <i class="icon--percent2"></i>
                            {/if}
                        </div>
                    </div>
                </div>
            {/block}

            {* Product information *}
            {block name='frontend_checkout_cart_item_discount_details'}
                <div class="panel--td table--content">

                    {* Product name *}
                    {block name='frontend_checkout_cart_item_discount_details_title'}
                        <span class="content--title">{$lineItem.label|strip_tags|truncate:60}</span>
                    {/block}

                    {* Additional product information *}
                    {block name='frontend_checkout_cart_item_discount_details_inline'}{/block}
                </div>
            {/block}
        </div>
    {/block}

    {* Product tax rate *}
    {block name='frontend_checkout_cart_item_discount_tax_price'}{/block}

    {* Accumulated product price *}
    {block name='frontend_checkout_cart_item_discount_total_sum'}
        <div class="panel--td table--column column--total-price block is--align-right">
            {block name='frontend_checkout_cart_item_discount_total_sum_label'}
                <div class="column--label total-price--label">
                    {s name="CartColumnTotal" namespace="frontend/checkout/cart_header"}{/s}
                </div>
            {/block}

            {block name='frontend_checkout_cart_item_discount_total_sum_display'}
                {$lineItem.price.totalPrice|currency}{block name='frontend_checkout_cart_tax_symbol'}{s name="Star" namespace="frontend/listing/box_article"}{/s}{/block}
            {/block}
        </div>
    {/block}
</div>
