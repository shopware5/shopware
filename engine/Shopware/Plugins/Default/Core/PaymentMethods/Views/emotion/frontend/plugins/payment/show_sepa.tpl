<div class="debit">
    <p class="none">
        <label>{s namespace="frontend/plugins/payment/sepa" name='PaymentSepaLabelIban'}{/s}:</label>
        <input class="input is--disabled" type="text" readonly="readonly" value="{$form_data.sSepaIban|escape}">
    </p>
    {if {config name=sepaShowBic}}
        <p class="none">
            <label>{s namespace="frontend/plugins/payment/sepa" name='PaymentSepaLabelBic'}{/s}:</label>
            <input class="input is--disabled" type="text" readonly="readonly" value="{$form_data.sSepaBic|escape}">
        </p>
    {/if}
    {if {config name=sepaShowBankName}}
        <p class="none">
            <label>{s namespace="frontend/plugins/payment/sepa" name='PaymentSepaLabelBankName'}{/s}:</label>
            <input class="input is--disabled" type="text" readonly="readonly" value="{$form_data.sSepaBankName|escape}">
        </p>
    {/if}
    {if {config name=sepaSendEmail}}
        <p class="none clearfix">
            <label>{s namespace="frontend/plugins/payment/sepa" name='PaymentSepaLabelUseBillingData'}{/s}:</label>
            <input disabled name="sSepaUseBillingData" type="checkbox"
                {if $form_data.sSepaUseBillingData === true}
                    checked="checked"
                {/if}
                class="checkbox"/>
        </p>
    {/if}

    <div class="space"></div>
    <a href="{url controller=account action=payment sTarget=$sTarget|default:"checkout" sTargetAction=$sTargetAction|default:"index"}" class="button-middle small">
        {s name="ConfirmLinkChangePayment" namespace="frontend/checkout/confirm_left"}{/s}
    </a>
</div>