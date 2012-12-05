{block name='frontend_checkout_cart_item_image' prepend}
    {if $sBasketItem.abo_attributes.swag_abo_commerce_duration > 0}
        <div class="item-abo-article"><span></span></div>
    {/if}
{/block}


{block name='frontend_checkout_cart_item_bundle_details'}
    {if $sBasketItem.abo_attributes.swag_abo_commerce_id > 0}
    <div class="item-abo-basket-discount">{* Contains the discount icon *}</div>
    <div class="grid_6">
        <div class="basket_details">
            <strong class="title">Abo-Rabatt</strong>

            <p class="ordernumber">
                {$sBasketItem.ordernumber}
            </p>
        </div>
    </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}


{block name='frontend_checkout_cart_item_details_inline'}
    {if $sBasketItem.abo_attributes.swag_abo_commerce_duration > 0}
    <p class="ordernumber">
        Laufzeit: {$sBasketItem.abo_attributes.swag_abo_commerce_duration}<br />
        Lieferinterval: {$sBasketItem.abo_attributes.swag_abo_commerce_delivery_interval}
    </p>
    {/if}
{/block}

