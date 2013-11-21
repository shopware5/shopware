<div class="debit">
    <p class="none">
        <label>{s namespace="engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa" name='PaymentSepaLabelIban'}{/s}:</label>
        <input class="input is--disabled" type="text" readonly="readonly" value="{if $form_data.sSepaIban}{$form_data.sSepaIban|escape}{elseif $form_data.paymentData}{$form_data.paymentData->getIban()|escape}{/if}">
    </p>
    {if {config name=sepaShowBic}}
        <p class="none">
            <label>{s namespace="engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa" name='PaymentSepaLabelBic'}{/s}:</label>
            <input class="input is--disabled" type="text" readonly="readonly" value="{if $form_data.sSepaBic}{$form_data.sSepaBic|escape}{elseif $form_data.paymentData}{$form_data.paymentData->getBic()|escape}{/if}">
        </p>
    {/if}
    {if {config name=sepaShowBankName}}
        <p class="none">
            <label>{s namespace="engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa" name='PaymentSepaLabelBankName'}{/s}:</label>
            <input class="input is--disabled" type="text" readonly="readonly" value="{if $form_data.sSepaBankName}{$form_data.sSepaBankName|escape}{elseif $form_data.paymentData}{$form_data.paymentData->getBankName()|escape}{/if}">
        </p>
    {/if}
    {if {config name=sepaSendEmail}}
        <p class="none clearfix">
            <label>{s namespace="engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/sepa" name='PaymentSepaLabelUseBillingData'}{/s}:</label>
            <input disabled name="sSepaUseBillingData" type="checkbox"
                {if $form_data.sSepaUseBillingData === 'true' || ($form_data.paymentData && $form_data.paymentData->getUseBillingData())}
                    checked="checked"
                {/if}
                class="checkbox"/>
        </p>
    {/if}

    <div class="space"></div>
    <a href="{url controller=account action=payment sTarget=checkout}" class="button-middle small">
        {s name="ConfirmLinkChangePayment" namespace="frontend/checkout/confirm_left"}{/s}
    </a>
</div>