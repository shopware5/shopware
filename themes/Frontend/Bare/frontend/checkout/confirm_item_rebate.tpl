{extends file='frontend/checkout/cart_item_rebate.tpl'}

{* Rebate tax price *}
{block name='frontend_checkout_cart_item_rebate_tax_price'}
    <div class="panel--td column--tax-price block is--align-right">
        {block name='frontend_checkout_cart_voucher_tax_label'}
            <div class="column--label tax-price--label">
                {if $sUserData.additional.charge_vat && !$sUserData.additional.show_net}
                    {s name='CheckoutColumnExcludeTax' namespace="frontend/checkout/confirm_header"}{/s}
                {elseif $sUserData.additional.charge_vat}
                    {s name='CheckoutColumnTax' namespace="frontend/checkout/confirm_header"}{/s}
                {/if}
            </div>
        {/block}

        {block name='frontend_checkout_cart_voucher_tax_value'}
            {if $sUserData.additional.charge_vat}
                {if $sBasketItem.proportion}
                    {foreach from=$sBasketItem.proportion item=proportion}
                        {$proportion.tax_rate}%: {$proportion.tax|currency}<br>
                    {/foreach}
                {else}
                    {$sBasketItem.tax|currency}
                {/if}
            {/if}
        {/block}
    </div>
{/block}

{* Hide tax symbols *}
{block name='frontend_checkout_cart_tax_symbol'}{/block}

{* Proportional info *}
{block name='frontend_checkout_cart_item_rebate_details_inline'}
    {if $sBasketItem.proportion}
        <div class="product--essential-features">
            {s name="ProportionalItemInfo" namespace="frontend/checkout/cart"}{/s}
        </div>
    {/if}
{/block}