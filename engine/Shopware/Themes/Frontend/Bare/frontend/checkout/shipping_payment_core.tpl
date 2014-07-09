{* Error messages *}
{block name='frontend_account_payment_error_messages'}
    {include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
{/block}

<div class="outer-confirm-container" data-ajax-shipping-payment="true">
    <form id="shippingPaymentForm" name="shippingPaymentForm" method="post" action="{url controller='checkout' action='saveShippingPayment' sTarget='checkout' sTargetAction='index'}" class="payment">
        <div class="shipping-payment-information grid_16 first">
            {* Payment method *}
            <div class="inner_container">
                {block name='frontend_checkout_shipping_payment_core_payment_fields'}
                    {include file='frontend/checkout/change_payment.tpl'}
                {/block}
            </div>
            {* Shipping method *}
            <div class="inner_container">
                {block name='frontend_checkout_shipping_payment_core_shipping_fields'}
                    {include file="frontend/checkout/change_shipping.tpl"}
                {/block}
            </div>
            {* Cart values *}
            <div class="inner_container">
                {block name='frontend_checkout_shipping_payment_core_footer'}
                    {include file="frontend/checkout/cart_footer.tpl"}
                {/block}
            </div>
        </div>

        {block name='frontend_checkout_shipping_payment_core_buttons'}
            <input type="submit" value="{s name='NextButton'}Next{/s}" class="button-right large right" />
        {/block}
    </form>
</div>