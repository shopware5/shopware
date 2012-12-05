{block name='frontend_checkout_cart_item_image' prepend}
    {if $sBasketItem.attribute.swagAboCommerceDuration > 0}
        <div class="item-abo-article"><span></span></div>
    {/if}
{/block}


{block name='frontend_checkout_cart_item_bundle_details'}
    <div class="item-abo-basket-discount">{* Contains the discount icon *}</div>
    <div class="grid_6">
        <div class="basket_details">
            <strong class="title">Abo-Rabatt</strong>

            <p class="ordernumber">
                {$sBasketItem.ordernumber}
            </p>
        </div>
    </div>
{/block}

{block name='frontend_checkout_cart_item_details_inline'}
    {if $sBasketItem.attribute.swagAboCommerceDuration > 0}
    <p class="ordernumber">
        Laufzeit: {$sBasketItem.attribute.swagAboCommerceDuration}<br />
        Lieferinterval: {$sBasketItem.attribute.swagAboCommerceDeliveryInterval}
    </p>
    {/if}
{/block}

