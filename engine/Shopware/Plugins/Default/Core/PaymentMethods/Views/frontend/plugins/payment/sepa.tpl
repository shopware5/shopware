<div class="debit">
    <p class="none">
        <label for="iban">{s name='PaymentSepaLabelIban'}{/s}*:</label>
        <input name="sSepaIban" type="text" id="iban"
               value="{if $form_data.sSepaIban}{$form_data.sSepaIban|escape}{elseif $form_data.paymentData}{$form_data.paymentData->getIban()|escape}{/if}"
               class="text {if $error_flags.sSepaIban}instyle_error{/if}"/>
    </p>
    {if {config name=sepaShowBic}}
        <p class="none">
            <label for="bic">{s name='PaymentSepaLabelBic'}{/s}{if {config name=sepaRequireBic}}*{/if}:</label>
            <input name="sSepaBic" type="text" id="bic"
                   value="{if $form_data.sSepaBic}{$form_data.sSepaBic|escape}{elseif $form_data.paymentData}{$form_data.paymentData->getBic()|escape}{/if}"
                   class="text {if $error_flags.sSepaBic}instyle_error{/if}"/>
        </p>
    {/if}
    {if {config name=sepaShowBankName}}
        <p class="none">
            <label for="bank">{s name='PaymentSepaLabelBankName'}{/s}{if {config name=sepaRequireBankName}}*{/if}
                :</label>
            <input name="sSepaBankName" type="text" id="bank"
                   value="{if $form_data.sSepaBankName}{$form_data.sSepaBankName|escape}{elseif $form_data.paymentData}{$form_data.paymentData->getBankName()|escape}{/if}"
                   class="text {if $error_flags.sSepaBankName}instyle_error{/if}"/>
        </p>
    {/if}
    <p class="none clearfix">
        <label for="usebilling">{s name='PaymentSepaLabelUseBillingData'}{/s}:</label>
        <input name="sSepaUseBillingData" type="checkbox" id="usebilling" value="true"
                {if $form_data.sSepaUseBillingData}
                    {if $form_data.sSepaUseBillingData === 'true'}
                        checked="checked"
                    {/if}
                {elseif $form_data.paymentData && $form_data.paymentData->getUseBillingData()}
                    checked="checked"
                {/if}
                class="checkbox {if $error_flags.sSepaBankHolder}instyle_error{/if}"/>
    </p>
    <div class="space"></div>
    <p class="description">{s name='PaymentSepaInfoFields'}{/s}
    </p>
</div>