<div class="debit">
    <p class="none">
        <input name="sDebitAccount" type="text" id="kontonr" {if $checked} required="required" aria-required="true"{/if} placeholder="{s namespace='frontend/plugins/payment/debit' name='PaymentDebitLabelAccount2'}{/s}" value="{$form_data.sDebitAccount|escape}" class="is--required{if $error_flags.sDebitAccount} has--error{/if}" />
    </p>

    <p class="none">
        <input name="sDebitBankcode" type="text" id="blz" {if $checked} required="required" aria-required="true"{/if} placeholder="{s namespace='frontend/plugins/payment/debit' name='PaymentDebitLabelBankcode2'}{/s}" value="{$form_data.sDebitBankcode|escape}" class="is--required{if $error_flags.sDebitBankcode} has--error{/if}" />
    </p>

    <p class="none">
        <input name="sDebitBankName" type="text" id="bank" {if $checked} required="required" aria-required="true"{/if} placeholder="{s namespace='frontend/plugins/payment/debit' name='PaymentDebitLabelBankname2'}{/s}" value="{$form_data.sDebitBankName|escape}" class="is--required{if $error_flags.sDebitBankName} has--error{/if}" />
    </p>

    <p class="none">
        <input name="sDebitBankHolder" type="text" id="bank2" {if $checked} required="required" aria-required="true"{/if} placeholder="{s namespace='frontend/plugins/payment/debit' name='PaymentDebitLabelName2'}{/s}" value="{$form_data.sDebitBankHolder|escape}" class="is--required{if $error_flags.sDebitBankHolder} has--error{/if}" />
    </p>
</div>