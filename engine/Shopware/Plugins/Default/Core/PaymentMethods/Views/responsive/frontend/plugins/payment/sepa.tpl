<div class="debit">
    <p class="none">
        <input name="sSepaIban" type="text" id="iban" {if $checked} required="required" aria-required="true"{/if} placeholder="{s namespace='frontend/plugins/payment/sepa' name='PaymentSepaLabelIban'}{/s}*" value="{$form_data.sSepaIban|escape}" class="is--required{if $error_flags.sSepaIban} has--error{/if}" />
    </p>
    {if {config name=sepaShowBic}}
        <p class="none">
            <input name="sSepaBic" type="text" id="bic" {if $checked} required="required" aria-required="true"{/if} placeholder="{s namespace='frontend/plugins/payment/sepa' name='PaymentSepaLabelBic'}{/s}{if {config name=sepaRequireBic}}*{/if}" value="{$form_data.sSepaBic|escape}" class="is--required{if $error_flags.sSepaBic} has--error{/if}" />
        </p>
    {/if}
    {if {config name=sepaShowBankName}}
        <p class="none">
            <input name="sSepaBankName" type="text" id="bank" {if $checked} required="required" aria-required="true"{/if} placeholder="{s namespace='frontend/plugins/payment/sepa' name='PaymentSepaLabelBankName'}{/s}{if {config name=sepaRequireBankName}}*{/if}" value="{$form_data.sSepaBankName|escape}" class="is--required{if $error_flags.sSepaBankName} has--error{/if}" />
        </p>
    {/if}
    {if {config name=sepaSendEmail}}
        <p class="none clearfix">
            <input name="sSepaUseBillingData" type="checkbox" id="usebilling" value="true"
                {if $form_data.sSepaUseBillingData === 'true' || (!$form_data.isPost && $form_data.sSepaUseBillingData !== false)}
                    checked="checked"
                {/if}
                class="checkbox {if $error_flags.sSepaBankHolder}instyle_error{/if}"/>
            <label for="usebilling"  style="float:none; width:100%; display:inline">{s namespace='frontend/plugins/payment/sepa' name='PaymentSepaLabelUseBillingData'}{/s}:</label>
        </p>
    {/if}
</div>