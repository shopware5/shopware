{namespace name="frontend/plugins/payment/sepa"}

<div class="debit">
    <p class="none">
        <input class="input is--disabled" type="text" placeholder="{s name='PaymentSepaLabelIban'}{/s}" readonly="readonly" value="{$form_data.sSepaIban|escape}">
    </p>
    {if {config name=sepaShowBic}}
        <p class="none">
            <input class="input is--disabled" type="text" placeholder="{s name='PaymentSepaLabelBic'}{/s}" readonly="readonly" value="{$form_data.sSepaBic|escape}">
        </p>
    {/if}
    {if {config name=sepaShowBankName}}
        <p class="none">
            <input class="input is--disabled" type="text" placeholder="{s name='PaymentSepaLabelBankName'}{/s}" readonly="readonly" value="{$form_data.sSepaBankName|escape}">
        </p>
    {/if}
    {if {config name=sepaSendEmail}}
        <p class="none">
            <input disabled name="sSepaUseBillingData" type="checkbox"{if $form_data.sSepaUseBillingData === true} checked="checked"{/if} class="checkbox"/>
            <label>{s name='PaymentSepaLabelUseBillingData'}{/s}</label>
        </p>
    {/if}

    <a href="{url controller=account action=payment sTarget=$sTarget|default:"checkout" sTargetAction=$sTargetAction|default:"index"}" class="btn is--secondary is--small">
        {s name="ConfirmLinkChangePayment" namespace="frontend/checkout/confirm"}{/s}
    </a>
</div>
