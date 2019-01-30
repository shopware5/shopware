<div class="actions">
    {* Continue shopping *}
    {block name="frontend_checkout_actions_link_last"}{/block}

    {if !$sMinimumSurcharge && ($sInquiry || $sDispatchNoOrder)}
        {block name="frontend_checkout_actions_inquiry"}
            {s name="CheckoutActionsLinkOffer" assign="snippetCheckoutActionsLinkOffer"}{/s}
        <a href="{$sInquiryLink}" title="{$snippetCheckoutActionsLinkOffer|escape}" class="button-middle large">
            {s name="CheckoutActionsLinkOffer"}{/s}
        </a>
        {/block}
    {/if}

    {* Checkout *}
    {if !$sMinimumSurcharge && !$sDispatchNoOrder}
        {block name="frontend_checkout_actions_confirm"}
            {s name="CheckoutActionsLinkProceed" assign="snippetCheckoutActionsLinkProceed"}{/s}
        <a href="{if {config name=always_select_payment}}{url controller='checkout' action='shippingPayment'}{else}{url controller='checkout' action='confirm'}{/if}" title="{$snippetCheckoutActionsLinkProceed|escape}" class="button-right large right checkout" >
            {s name="CheckoutActionsLinkProceed"}{/s}
        </a>
        {/block}
    {/if}

    <div class="clear">&nbsp;</div>
</div>
