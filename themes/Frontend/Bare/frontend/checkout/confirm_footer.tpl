{extends file='frontend/checkout/cart_footer.tpl'}

{block name='frontend_checkout_cart_footer_field_labels_taxes'}
    {$smarty.block.parent}
    {if {config name=countrynotice} && $sCountry.notice && {include file="string:{$sCountry.notice}"} !== ""}
        <li class="list--entry table-footer--country-notice">
            {* Include country specific notice message *}
            <p>{include file='string:{$sCountry.notice}'}</p>
        </li>
    {/if}

    {if !$sUserData.additional.charge_vat && {config name=nettonotice}}
        <li class="list--entry table-footer--netto-notice">
        {include file="frontend/_includes/messages.tpl" type="warning" content="*{s name='CheckoutFinishTaxInformation'}{/s}"}
        </li>
    {/if}
{/block}

{block name='frontend_checkout_cart_footer_add_product'}{/block}

{block name='frontend_checkout_cart_footer_add_voucher'}
    {if {config name=showVoucherModeForCheckout} != 0}
        <form method="post" action="{url action='addVoucher' sTargetAction=$sTargetAction}"
                          class="table--add-voucher add-voucher--form">
            {if {config name=showVoucherModeForCheckout} == 1}
                {block name='frontend_checkout_cart_footer_add_voucher_trigger'}
                    <input type="checkbox" id="add-voucher--trigger" class="add-voucher--checkbox">
                {/block}

                {block name='frontend_checkout_cart_footer_add_voucher_label'}
                    <label for="add-voucher--trigger"
                           class="add-voucher--label">{s name="CheckoutFooterVoucherTrigger" namespace="frontend/checkout/cart_footer"}{/s}</label>
                {/block}
            {/if}

            <div class="add-voucher--panel {if {config name=showVoucherModeForCheckout} == 1}is--hidden {/if}block-group">
                {block name='frontend_checkout_cart_footer_add_voucher_field'}
    {s name="CheckoutFooterAddVoucherLabelInline" namespace="frontend/checkout/cart_footer" assign="snippetCheckoutFooterAddVoucherLabelInline"}{/s}
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
