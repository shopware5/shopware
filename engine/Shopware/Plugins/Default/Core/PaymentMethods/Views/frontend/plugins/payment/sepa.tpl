<div class="debit">
    <p class="none">
        <label for="iban">{s namespace='frontend/plugins/payment/sepa' name='PaymentSepaLabelIban'}{/s}*:</label>
        <input name="sSepaIban" type="text" id="iban"
               value="{$form_data.sSepaIban|escape}"
               class="text {if $error_flags.sSepaIban}instyle_error{/if}"/>
    </p>
    {if {config name=sepaShowBic}}
        <p class="none">
            <label for="bic">{s namespace='frontend/plugins/payment/sepa' name='PaymentSepaLabelBic'}{/s}{if {config name=sepaRequireBic}}*{/if}:</label>
            <input name="sSepaBic" type="text" id="bic"
                   value="{$form_data.sSepaBic|escape}"
                   class="text {if $error_flags.sSepaBic}instyle_error{/if}"/>
        </p>
    {/if}
    {if {config name=sepaShowBankName}}
        <p class="none">
            <label for="bank">{s namespace='frontend/plugins/payment/sepa' name='PaymentSepaLabelBankName'}{/s}{if {config name=sepaRequireBankName}}*{/if}
                :</label>
            <input name="sSepaBankName" type="text" id="bank"
                   value="{$form_data.sSepaBankName|escape}"
                   class="text {if $error_flags.sSepaBankName}instyle_error{/if}"/>
        </p>
    {/if}
    {if {config name=sepaSendEmail}}
        <p class="none clearfix">
            <label for="usebilling">{s namespace='frontend/plugins/payment/sepa' name='PaymentSepaLabelUseBillingData'}{/s}:</label>
            <input name="sSepaUseBillingData" type="checkbox" id="usebilling" value="true"
                {if $form_data.sSepaUseBillingData === 'true' || (!$form_data.isPost && $form_data.sSepaUseBillingData !== false)}
                    checked="checked"
                {/if}
                class="checkbox {if $error_flags.sSepaBankHolder}instyle_error{/if}"/>
        </p>
    {/if}
    <div class="space"></div>
    <p class="description">{s namespace='frontend/plugins/payment/sepa' name='PaymentSepaInfoFields'}{/s}
    </p>
</div>