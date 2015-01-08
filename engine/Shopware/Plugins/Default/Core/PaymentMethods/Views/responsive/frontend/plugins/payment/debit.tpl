{namespace name='frontend/plugins/payment/debit'}

<div class="payment--form-group">
    <input name="sDebitAccount"
           type="text"
           id="kontonr"
           {if $payment_mean.id == $form_data.payment}required="required" aria-required="true"{/if}
           placeholder="{s name='PaymentDebitPlaceholderAccount'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}"
           value="{$form_data.sDebitAccount|escape}"
           class="payment--field is--required{if $error_flags.sDebitAccount} has--error{/if}" />

    <input name="sDebitBankcode"
           type="text"
           id="blz"
           {if $payment_mean.id == $form_data.payment}required="required" aria-required="true"{/if}
           placeholder="{s name='PaymentDebitPlaceholderBankcode'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}"
           value="{$form_data.sDebitBankcode|escape}"
           class="payment--field is--required{if $error_flags.sDebitBankcode} has--error{/if}" />

    <input name="sDebitBankName"
           type="text"
           id="bank"
           {if $payment_mean.id == $form_data.payment}required="required" aria-required="true"{/if}
           placeholder="{s name='PaymentDebitPlaceholderBankname'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}"
           value="{$form_data.sDebitBankName|escape}"
           class="payment--field is--required{if $error_flags.sDebitBankName} has--error{/if}" />

    <input name="sDebitBankHolder"
           type="text"
           id="bank2"
           {if $payment_mean.id == $form_data.payment}required="required" aria-required="true"{/if}
           placeholder="{s name='PaymentDebitPlaceholderName'}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}"
           value="{$form_data.sDebitBankHolder|escape}"
           class="payment--field is--required{if $error_flags.sDebitBankHolder} has--error{/if}" />

    {block name='frontend_checkout_payment_required'}
        {* Required fields hint *}
        <div class="register--required-info">
            {s name='RegisterPersonalRequiredText' namespace='frontend/register/personal_fieldset'}{/s}
        </div>
    {/block}
</div>