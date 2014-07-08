{namespace name="frontend/checkout/cart_item"}

<div class="table--row block-group row--premium-product">

    {* Product information column *}
    {block name='frontend_checkout_cart_item_premium_name'}
        <div class="table--column column--product block">

            {* Badge *}
            {block name='frontend_checkout_cart_item_premium_image'}
				<div class="table--media">
					<div class="basket--badge">
						{s name="CartItemInfoFree"}{/s}
					</div>
				</div>
            {/block}

            {* Product information *}
            {block name='frontend_checkout_cart_item_premium_details'}
                <div class="table--content">

                    {* Product name *}
                    {block name='frontend_checkout_cart_item_premium_premium_details_title'}
                       <span class="content--title">
                            {$sBasketItem.articlename|strip_tags|truncate:60}
                        </span>
                    {/block}

                    {* Product SKU number *}
                    {block name='frontend_checkout_cart_item_premium_details_sku'}
                        <p class="content--thank-you content">
                            {s name="CartItemInfoPremium"}{/s}
                        </p>
                    {/block}

                    {* Additional product information *}
                    {block name='frontend_checkout_cart_item_premium_details_inline'}{/block}
                </div>
            {/block}
        </div>
    {/block}

	{* Product quantity *}
    {block name='frontend_checkout_cart_item_premium_quantity'}
        <div class="table--column column--quantity block is--align-right">
			{* Label *}
			{block name='frontend_checkout_cart_item_premium_quantity_label'}
				<div class="column--label quantity--label">
					{s name="CartColumnQuantity" namespace="frontend/checkout/cart_header"}{/s}
				</div>
			{/block}

			<select name="sQuantity">
				<option selected="selected" disabled="disabled">
					1
				</option>
			</select>
        </div>
    {/block}

    {* Product tax rate *}
    {block name='frontend_checkout_cart_item_premium_tax_price'}{/block}

    {* Accumulated product price *}
    {block name='frontend_checkout_cart_item_premium_total_sum'}
        <div class="table--column column--total-price block is--align-right">
			{block name='frontend_checkout_cart_item_premium_total_sum_label'}
				<div class="column--label total-price--label">
					{s name="CartColumnTotal" namespace="frontend/checkout/cart_header"}{/s}
				</div>
			{/block}

            {block name='frontend_checkout_cart_item_premium_total_sum_display'}
                {s name="CartItemInfoFree"}{/s}
            {/block}
        </div>
    {/block}

    {* Remove product from basket *}
    {block name='frontend_checkout_cart_item_premium_delete_article'}
        <div class="table--column column--actions block">
            <a href="{url action='deleteArticle' sDelete=$sBasketItem.id sTargetAction=$sTargetAction}" class="btn is--small" title="{s name='CartItemLinkDelete '}{/s}">
				<i class="icon--cross"></i>
            </a>
        </div>
    {/block}
</div>