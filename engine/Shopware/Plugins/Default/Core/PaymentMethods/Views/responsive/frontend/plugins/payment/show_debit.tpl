{namespace name="frontend/plugins/payment/debit"}

<div class="debit">
    <p class="none">
        <input class="input is--disabled" type="text" placeholder="{s name='PaymentDebitLabelAccount'}{/s}" readonly="readonly" value="{$form_data.sDebitAccount|escape}">
    </p>

    <p class="none">
        <input class="input is--disabled" type="text" placeholder="{s name='PaymentDebitLabelBankcode'}{/s}" readonly="readonly" value="{$form_data.sDebitBankcode|escape}">
    </p>

    <p class="none">
        <input class="input is--disabled" type="text" placeholder="{s name='PaymentDebitLabelBankname'}{/s}" readonly="readonly" value="{$form_data.sDebitBankName|escape}">
    </p>

    <p class="none">
        <input class="input is--disabled" type="text" placeholder="{s name='PaymentDebitLabelName'}{/s}" readonly="readonly" value="{$form_data.sDebitBankHolder|escape}">
    </p>

    <a href="{url controller=account action=payment sTarget=$sTarget|default:"checkout" sTargetAction=$sTargetAction|default:"index"}" class="btn is--secondary is--small">
        {s name="ConfirmLinkChangePayment" namespace="frontend/checkout/confirm"}{/s}
    </a>
</div>