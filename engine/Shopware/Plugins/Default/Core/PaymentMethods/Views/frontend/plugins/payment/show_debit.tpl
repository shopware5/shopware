<div class="debit">
    <p class="none">
        <label>{s namespace="engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/debit" name='PaymentDebitLabelAccount'}{/s}</label>
        <input class="input is--disabled" type="text" readonly="readonly" value="{$form_data.sDebitAccount|escape}">
    </p>

    <p class="none">
        <label>{s namespace="engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/debit" name='PaymentDebitLabelBankcode'}{/s}</label>
        <input class="input is--disabled" type="text" readonly="readonly" value="{$form_data.sDebitBankcode|escape}">
    </p>

    <p class="none">
        <label>{s namespace="engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/debit" name='PaymentDebitLabelBankname'}{/s}</label>
        <input class="input is--disabled" type="text" readonly="readonly" value="{$form_data.sDebitBankName|escape}">
    </p>

    <p class="none">
        <label>{s namespace="engine/Shopware/Plugins/Default/Core/PaymentMethods/Views/frontend/plugins/payment/debit" name='PaymentDebitLabelName'}{/s}</label>
        <input class="input is--disabled" type="text" readonly="readonly" value="{$form_data.sDebitBankHolder|escape}">
    </p>

    <div class="space"></div>
    <a href="{url controller=account action=payment sTarget=checkout}" class="button-middle small">
        {s name="ConfirmLinkChangePayment" namespace="frontend/checkout/confirm_left"}{/s}
    </a>
</div>