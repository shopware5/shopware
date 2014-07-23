{namespace name="frontend/checkout/cart_item"}

<div class="table--row block-group row--product">
    <form name="basket_change_quantity{$sBasketItem.id}" method="post" action="{url action='changeQuantity' sTargetAction=$sTargetAction}">

        {* Product information column *}
        {block name='frontend_checkout_cart_item_name'}
            <div class="table--column column--product block">

                {* Product image *}
                {block name='frontend_checkout_cart_item_image'}
                    <div class="table--media">
                        {if $sBasketItem.image.src.2}
                            <a href="{$sBasketItem.linkDetails}" title="{$sBasketItem.articlename|strip_tags}" class="table--media-link">
                                <img src="{$sBasketItem.image.src.2}" alt="{$sBasketItem.articlename}" />
                            </a>
                        {else}
                            <img class="table--media" src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{$sBasketItem.articlename}" />
                        {/if}
                    </div>
                {/block}

                {* Product information *}
                {block name='frontend_checkout_cart_item_details'}
                    <div class="table--content">

                        {* Product name *}
                        {block name='frontend_checkout_cart_item_details_title'}
                            <a class="content--title" href="{$sBasketItem.linkDetails}" title="{$sBasketItem.articlename|strip_tags}">
                                {$sBasketItem.articlename|strip_tags|truncate:60}
                            </a>
                        {/block}

                        {* Product SKU number *}
                        {block name='frontend_checkout_cart_item_details_sku'}
                            <p class="content--sku content">
                                {s name="CartItemInfoId"}{/s} {$sBasketItem.ordernumber}
                            </p>
                        {/block}

                        {* Product delivery information *}
                        {block name='frontend_checkout_cart_item_delivery_informations'}
                            {if {config name=BasketShippingInfo} && $sBasketItem.shippinginfo}
                                {include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sBasketItem}
                            {/if}
                        {/block}

                        {* Additional product information *}
                        {block name='frontend_checkout_cart_item_details_inline'}{/block}
                    </div>
                {/block}
            </div>
        {/block}

        {* Product quantity *}
        {block name='frontend_checkout_cart_item_quantity'}
            <div class="table--column column--quantity block is--align-right">

				{* Label *}
				{block name='frontend_checkout_cart_item_quantity_label'}
					<div class="column--label quantity--label">
						{s name="CartColumnQuantity" namespace="frontend/checkout/cart_header"}{/s}
					</div>
				{/block}

                <input type="hidden" name="sArticle" value="{$sBasketItem.id}" />
                {block name='frontend_checkout_cart_item_quantity_selection'}
                    <select name="sQuantity" data-auto-submit-form="true">
                        {section name="i" start=$sBasketItem.minpurchase loop=$sBasketItem.maxpurchase+1 step=$sBasketItem.purchasesteps}
                            <option value="{$smarty.section.i.index}" {if $smarty.section.i.index==$sBasketItem.quantity}selected="selected"{/if}>
                                {$smarty.section.i.index}
                            </option>
                        {/section}
                    </select>
                {/block}
            </div>
        {/block}

		{* Product unit price *}
        {block name='frontend_checkout_cart_item_price'}
            <div class="table--column column--unit-price block is--align-right">

                {if !$sBasketItem.modus}
					{block name='frontend_checkout_cart_item_unit_price_label'}
						<div class="column--label unit-price--label">
							{s name="CartColumnPrice" namespace="frontend/checkout/cart_header"}{/s}
						</div>
					{/block}

					{$sBasketItem.price|currency}{block name='frontend_checkout_cart_tax_symbol'}{s name="Star" namespace="frontend/listing/box_article"}{/s}{/block}
                {/if}
            </div>
        {/block}

        {* Product tax rate *}
        {block name='frontend_checkout_cart_item_tax_price'}{/block}

        {* Accumulated product price *}
        {block name='frontend_checkout_cart_item_total_sum'}
            <div class="table--column column--total-price block is--align-right">
				{block name='frontend_checkout_cart_item_total_price_label'}
					<div class="column--label total-price--label">
						{s name="CartColumnTotal" namespace="frontend/checkout/cart_header"}{/s}
					</div>
				{/block}
                {$sBasketItem.amount|currency}{block name='frontend_checkout_cart_tax_symbol'}{s name="Star" namespace="frontend/listing/box_article"}{/s}{/block}
            </div>
        {/block}

        {* Remove product from basket *}
        {block name='frontend_checkout_cart_item_delete_article'}
            <div class="table--column column--actions block">
                <a href="{url action='deleteArticle' sDelete=$sBasketItem.id sTargetAction=$sTargetAction}" class="btn is--small" title="{s name='CartItemLinkDelete '}{/s}">
                	<i class="icon--cross"></i>
                </a>
            </div>
        {/block}
    </form>
</div>