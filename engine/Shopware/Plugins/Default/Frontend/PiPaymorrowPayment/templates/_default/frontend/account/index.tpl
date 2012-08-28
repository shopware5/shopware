{block name="frontend_account_index_payment_method"}
    {if $sUserData.additional.payment.name=="PaymorrowRate" || $sUserData.additional.payment.name=="PaymorrowInvoice"}
    <div class="grid_8 last" id="selected_payment">
        <h2 class="headingbox_dark largesize">Gew&auml;hlte Zahlungsart</h2>

        <div class="inner_container">
            <p>
                <strong>{$sUserData.additional.payment.description}</strong><br/>
                {if $sUserData.additional.payment.name=="PaymorrowRate"}
                    {$pi_Paymorrow_lang['rate']['boxtext']}
                    {elseif $sUserData.additional.payment.name=="PaymorrowInvoice"}
                    {$pi_Paymorrow_lang['invoice']['boxtext']}
                {/if}
            </p>

            <div class="change">
                <a href="{url controller='account' action='payment'}" title="Zahlungsart &auml;ndern"
                   class="button-middle small">
                    Zahlungsart &auml;ndern
                </a>
            </div>
        </div>
    </div>
        {else}
        {$smarty.block.parent}
    {/if}
{/block}   
