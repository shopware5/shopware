{* Basket informations *}
{block name='frontend_checkout_error_messages_basket_error'}
    {if $sBasketInfo}
        {include file="frontend/_includes/messages.tpl" type="error" content=$sBasketInfo}
    {/if}
{/block}

{block name='frontend_checkout_error_messages_voucher_error'}
    {* Voucher error *}
    {if $sVoucherError}
        {include file="frontend/_includes/messages.tpl" type="error" content=$sVoucherError[0]}
    {/if}
{/block}

{block name="frontend_checkout_error_payment_blocked"}
    {if $paymentBlocked}
        {s name="ConfirmInfoPaymentBlocked" assign="snippetConfirmInfoPaymentBlocked"}{/s}
        {include file="frontend/_includes/messages.tpl" type="error" content=$snippetConfirmInfoPaymentBlocked}
    {/if}
{/block}

{block name="frontend_checkout_error_messages_esd_note"}
    {if $sShowEsdNote}
        {s name="ConfirmInfoPaymentNotCompatibleWithESD" assign="snippetConfirmInfoPaymentNotCompatibleWithESD"}{/s}
        {include file="frontend/_includes/messages.tpl" type="warning" content=$snippetConfirmInfoPaymentNotCompatibleWithESD}
    {/if}
{/block}

{block name='frontend_checkout_error_messages_no_shipping'}
    {if $sDispatchNoOrder}
        {s name="ConfirmInfoNoDispatch" assign="snippetConfirmInfoNoDispatch"}{/s}
        {include file="frontend/_includes/messages.tpl" type="warning" content=$snippetConfirmInfoNoDispatch}
    {/if}
{/block}

{* Minimum sum not reached *}
{block name='frontend_checkout_error_messages_minimum_not_reached'}
    {if $sMinimumSurcharge}
        {s name="ConfirmInfoMinimumSurcharge" assign="snippetConfirmInfoMinimumSurcharge"}{/s}
        {include file="frontend/_includes/messages.tpl" type="error" content=$snippetConfirmInfoMinimumSurcharge}
    {/if}
{/block}

{* Service article tos not accepted *}
{block name='frontend_checkout_error_messages_service_error'}
    {if $agreementErrors && $agreementErrors.serviceError}
        {s name="ServiceErrorMessage" assign="snippetServiceErrorMessage"}{/s}
        {include file="frontend/_includes/messages.tpl" type="error" content=$snippetServiceErrorMessage}
    {/if}
{/block}

{* ESD article tos not accepted *}
{block name='frontend_checkout_error_messages_esd_error'}
    {if $agreementErrors && $agreementErrors.esdError && {config name="showEsdWarning"}}
        {s name="EsdErrorMessage" assign="snippetEsdErrorMessage"}{/s}
        {include file="frontend/_includes/messages.tpl" type="error" content=$snippetEsdErrorMessage}
    {/if}
{/block}

{* Product with invalid category *}
{block name="frontend_checkout_error_messages_product_with_invalid_category"}
    {if $sInvalidCartItems}
        {s name="InvalidCategoryMessage" assign="snippetInvalidCategory"}{/s}
        {include file="frontend/_includes/messages.tpl" type="warning" content=$snippetInvalidCategory}
    {/if}
{/block}

{block name="frontend_checkout_error_messages_voucher_got_removed"}
    {if $sBasketVoucherRemovedInCart}
        {s name="InvalidVoucherGotRemoved" assign="snippetInvalidVoucherGotRemoved"}{/s}
        {include file="frontend/_includes/messages.tpl" type="warning" content=$snippetInvalidVoucherGotRemoved remoteMessageLink={url removeMessage="voucher"}}
    {/if}
{/block}


