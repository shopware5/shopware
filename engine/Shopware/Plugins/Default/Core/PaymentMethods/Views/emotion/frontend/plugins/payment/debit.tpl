<div class="debit">
    <p class="none">
        <label for="kontonr">{s namespace='frontend/plugins/payment/debit' name='PaymentDebitLabelAccount'}{/s}</label>
        <input name="sDebitAccount" type="text" id="kontonr" value="{$form_data.sDebitAccount|escape}"
               class="text {if $error_flags.sDebitAccount}instyle_error{/if}"/>
    </p>

    <p class="none">
        <label for="blz">{s namespace='frontend/plugins/payment/debit' name='PaymentDebitLabelBankcode'}{/s}</label>
        <input name="sDebitBankcode" type="text" id="blz" value="{$form_data.sDebitBankcode|escape}"
               class="text {if $error_flags.sDebitBankcode}instyle_error{/if}"/>
    </p>

    <p class="none">
        <label for="bank">{s namespace='frontend/plugins/payment/debit' name='PaymentDebitLabelBankname'}{/s}</label>
        <input name="sDebitBankName" type="text" id="bank" value="{$form_data.sDebitBankName|escape}"
               class="text {if $error_flags.sDebitBankName}instyle_error{/if}"/>
    </p>

    <p class="none">
        <label for="bank2">{s namespace='frontend/plugins/payment/debit' name='PaymentDebitLabelName'}{/s}</label>
        <input name="sDebitBankHolder" type="text" id="bank2" value="{$form_data.sDebitBankHolder|escape}"
               class="text {if $error_flags.sDebitBankHolder}instyle_error{/if}"/>
    </p>

    <p class="description">{s namespace='frontend/plugins/payment/debit' name='PaymentDebitInfoFields'}{/s}
    </p>
</div>
