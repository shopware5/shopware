{namespace name='frontend/plugins/payment/debit'}

<div class="debit">
    <p class="none">
        <input name="sDebitAccount" type="text" id="kontonr"{if $checked} required="required" aria-required="true"{/if} placeholder="{s name='PaymentDebitLabelAccount'}{/s}" value="{$form_data.sDebitAccount|escape}" class="is--required{if $error_flags.sDebitAccount} has--error{/if}" />
    </p>

    <p class="none">
        <input name="sDebitBankcode" type="text" id="blz"{if $checked} required="required" aria-required="true"{/if} placeholder="{s name='PaymentDebitLabelBankcode'}{/s}" value="{$form_data.sDebitBankcode|escape}" class="is--required{if $error_flags.sDebitBankcode} has--error{/if}" />
    </p>

    <p class="none">
        <input name="sDebitBankName" type="text" id="bank"{if $checked} required="required" aria-required="true"{/if} placeholder="{s name='PaymentDebitLabelBankname'}{/s}" value="{$form_data.sDebitBankName|escape}" class="is--required{if $error_flags.sDebitBankName} has--error{/if}" />
    </p>

    <p class="none">
        <input name="sDebitBankHolder" type="text" id="bank2"{if $checked} required="required" aria-required="true"{/if} placeholder="{s name='PaymentDebitLabelName'}{/s}" value="{$form_data.sDebitBankHolder|escape}" class="is--required{if $error_flags.sDebitBankHolder} has--error{/if}" />
    </p>
</div>