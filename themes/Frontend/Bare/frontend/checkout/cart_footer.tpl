{* Add product using the sku *}
{block name='frontend_checkout_cart_footer_add_product'}
    <form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}"
          class="table--add-product add-product--form block-group">

        {block name='frontend_checkout_cart_footer_add_product_field'}
            <input name="sAdd" class="add-product--field block" type="text"
                   placeholder="{s name='CheckoutFooterAddProductPlaceholder' namespace='frontend/checkout/cart_footer_left'}{/s}"/>
        {/block}

        {block name='frontend_checkout_cart_footer_add_product_button'}
            <button type="submit" class="add-product--button btn is--primary is--center block">
                <i class="icon--arrow-right"></i>
            </button>
        {/block}
    </form>
{/block}

{block name='frontend_checkout_cart_footer_element'}
    <div class="basket--footer">
        <div class="table--aggregation">
            {* Add product using a voucher *}
            {block name='frontend_checkout_cart_footer_add_voucher'}
                {if {config name=showVoucherModeForCart} != 0}
                    <form method="post" action="{url action='addVoucher' sTargetAction=$sTargetAction}"
                          class="table--add-voucher add-voucher--form">
                        {if {config name=showVoucherModeForCart} == 1}
                            {block name='frontend_checkout_cart_footer_add_voucher_trigger'}
                                <input type="checkbox" id="add-voucher--trigger" class="add-voucher--checkbox">
                            {/block}

                            {block name='frontend_checkout_cart_footer_add_voucher_label'}
                                <label for="add-voucher--trigger"
                                       class="add-voucher--label">{s name="CheckoutFooterVoucherTrigger"}{/s}</label>
                            {/block}
                        {/if}

                        <div class="add-voucher--panel {if {config name=showVoucherModeForCart} == 1}is--hidden {/if}block-group">
                            {block name='frontend_checkout_cart_footer_add_voucher_field'}
                                {s name="CheckoutFooterAddVoucherLabelInline" assign="snippetCheckoutFooterAddVoucherLabelInline"}{/s}
                                <input type="text" class="add-voucher--field is--medium block" name="sVoucher"
                                       placeholder="{$snippetCheckoutFooterAddVoucherLabelInline|escape}"/>
                            {/block}

                            {block name='frontend_checkout_cart_footer_add_voucher_button'}
                                <button type="submit"
                                        class="add-voucher--button is--medium btn is--primary is--center block">
                                    <i class="icon--arrow-right"></i>
                                </button>
                            {/block}
                        </div>
                    </form>
                {/if}
            {/block}

            {* Shipping costs pre-calculation *}
            {if $sBasket.content && !$sUserLoggedIn && !$sUserData.additional.user.id && {config name=basketShowCalculation} != 0}

                {block name='frontend_checkout_shipping_costs_country_trigger'}
                    {if {config name=basketShowCalculation} == 1}
                        <a href="#show-hide--shipping-costs" class="table--shipping-costs-trigger">
                            {s name='CheckoutFooterEstimatedShippingCosts'}{/s}
                            <i class="icon--arrow-right"></i>
                        </a>
                    {/if}
                {/block}

                {block name='frontend_checkout_shipping_costs_country_include'}
                    {if {config name=basketShowCalculation} == 2}
                        <span class="is--bold">{s name='CheckoutFooterEstimatedShippingCosts'}{/s}</span>
                    {/if}
                    {include file="frontend/checkout/shipping_costs.tpl" calculateShippingCosts=$calculateShippingCosts == true || {config name=basketShowCalculation} == 2}
                {/block}
            {/if}
        </div>

        {block name='frontend_checkout_cart_footer_field_labels'}
            <ul class="aggregation--list">

                {* Basket sum *}
                {block name='frontend_checkout_cart_footer_field_labels_sum'}
                    <li class="list--entry block-group entry--sum">

                        {block name='frontend_checkout_cart_footer_field_labels_sum_label'}
                            <div class="entry--label block">
                                {s name="CartFooterLabelSum"}{/s}
                            </div>
                        {/block}

                        {block name='frontend_checkout_cart_footer_field_labels_sum_value'}
                            <div class="entry--value block">
                                {$sBasket.Amount|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}
                            </div>
                        {/block}
                    </li>
                {/block}

                {* Shipping costs *}
                {block name='frontend_checkout_cart_footer_field_labels_shipping'}
                    <li class="list--entry block-group entry--shipping">

                        {block name='frontend_checkout_cart_footer_field_labels_shipping_label'}
                            <div class="entry--label block">
                                {s name="CartFooterLabelShipping"}{/s}
                            </div>
                        {/block}

                        {block name='frontend_checkout_cart_footer_field_labels_shipping_value'}
                            <div class="entry--value block">
                                {$sShippingcosts|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}
                            </div>
                        {/block}
                    </li>
                {/block}

                {* Total sum *}
                {block name='frontend_checkout_cart_footer_field_labels_total'}
                    <li class="list--entry block-group entry--total">

                        {block name='frontend_checkout_cart_footer_field_labels_total_label'}
                            <div class="entry--label block">
                                {s name="CartFooterLabelTotal"}{/s}
                            </div>
                        {/block}

                        {block name='frontend_checkout_cart_footer_field_labels_total_value'}
                            <div class="entry--value block is--no-star">
                                {if $sAmountWithTax && $sUserData.additional.charge_vat}{$sAmountWithTax|currency}{else}{$sAmount|currency}{/if}
                            </div>
                        {/block}
                    </li>
                {/block}

                {* Total net *}
                {block name='frontend_checkout_cart_footer_field_labels_totalnet'}
                    {if $sUserData.additional.charge_vat}
                        <li class="list--entry block-group entry--totalnet">

                            {block name='frontend_checkout_cart_footer_field_labels_totalnet_label'}
                                <div class="entry--label block">
                                    {s name="CartFooterTotalNet"}{/s}
                                </div>
                            {/block}

                            {block name='frontend_checkout_cart_footer_field_labels_totalnet_value'}
                                <div class="entry--value block is--no-star">
                                    {$sAmountNet|currency}
                                </div>
                            {/block}
                        </li>
                    {/if}
                {/block}

                {* Taxes *}
                {block name='frontend_checkout_cart_footer_field_labels_taxes'}
                    {if $sUserData.additional.charge_vat}
                        {foreach $sBasket.sTaxRates as $rate => $value}
                            {block name='frontend_checkout_cart_footer_field_labels_taxes_entry'}
                                <li class="list--entry block-group entry--taxes">

                                    {block name='frontend_checkout_cart_footer_field_labels_taxes_label'}
                                        <div class="entry--label block">
                                            {s name="CartFooterTotalTax"}{/s}
                                        </div>
                                    {/block}

                                    {block name='frontend_checkout_cart_footer_field_labels_taxes_value'}
                                        <div class="entry--value block is--no-star">
                                            {$value|currency}
                                        </div>
                                    {/block}
                                </li>
                            {/block}
                        {/foreach}
                    {/if}
                {/block}
            </ul>
        {/block}
    </div>
{/block}
