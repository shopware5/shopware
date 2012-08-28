{block name="frontend_index_content_top"}
    {if $sPayment.name=='PaymorrowInvoice' ||$sPayment.name=='PaymorrowRate'}
    <div class="grid_20 first">
    {* Step box *}
    {include file="frontend/register/steps.tpl" sStepActive="finished"}
    {* Check order headline *}
        {if $pi_Paymorrow_Viewport != 'PiPaymentPaymorrow' && $pi_Paymorrow_Viewport == 'checkout' && ($pi_Paymorrow_actions!='finish'  && $pi_Paymorrow_actions!='payment')}
            <div class="check_order">
                <h2 class="headingbox">{$pi_Paymorrow_lang['payment_info']['header']}</h2>

                <div class="inner_container">
                {* Payment informations *}
                    <p>
                        {if $sPayment.name == "PaymorrowInvoice"}
                            {$pi_Paymorrow_lang['payment_info']['first']['invoice']} 
                        {elseif $sPayment.name == "PaymorrowRate"}
                            {$pi_Paymorrow_lang['payment_info']['first']['rate']} 
                        {/if}
                        {$pi_Paymorrow_lang['payment_info']['second']} 
                        {$pi_Paymorrow_lang['payment_info']['third']}
                    </p>
                </div>
            </div>
        {/if}
    </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_checkout_confirm_submit' append}
    {if $sPayment.name=="PaymorrowRate" || $sPayment.name=="PaymorrowInvoice"}
    
    {* Submit order button *}
    <div class="actions" id="Paymorrowactions">
        <input type="submit" class="button-right large" id="basketButton_Paymorrow" onclick="loading()"
               value="{s name='ConfirmDoPayment'}Zahlung durchf&uuml;hren{/s}"/>

        <div id="Paymorrow_loadingscreen"
             style="display:none; right:10px; bottom:20px; font-weight:bold;font-size:14px;">
            Die Bestellung wird an Paymorrow gesendet...&nbsp;<img style="top:10px; " src="{link file='engine/Shopware/Plugins/Default/Frontend/PiPaymorrowPayment/img/ajax-loader.gif' fullPath}"/>
        </div>
    </div>
    <script type="text/javascript">
        function loading() {
            document.getElementById("basketButton_Paymorrow").style.display = "none";
            document.getElementById("Paymorrow_loadingscreen").style.display = "block";
//            document.getElementById("Paymorrowactions").style.width = "370px";
//            document.getElementById("Paymorrowactions").style.height = "40px";
        }
    </script>
    {else}
        {$smarty.block.parent}
     {/if}   
{/block}

{* Payment method *}
{block name='frontend_checkout_confirm_left_payment_method'}
    {if $sPayment.name=="PaymorrowRate" || $sPayment.name=="PaymorrowInvoice"}
        {if !$sRegisterFinished}
            <div class="payment-display">
                <h3 class="underline">{s name="ConfirmHeaderPayment" namespace="frontend/checkout/confirm_left"}{/s}</h3>
                <p>
                    <strong>{$sUserData.additional.payment.description}</strong>
                    <br />
                    {if $sPayment.name=="PaymorrowRate"}
                            {$pi_Paymorrow_lang['rate']['boxtext']}
                            {elseif $sPayment.name=="PaymorrowInvoice"}
                            {$pi_Paymorrow_lang['invoice']['boxtext']}
                        {/if}    
                    {if !$sUserData.additional.payment.esdactive}
                        <br />{s name="ConfirmInfoInstantDownload" namespace="frontend/checkout/confirm_left"}{/s}
                    {/if}
                </p>
    
                {* Action buttons *}
                <div class="actions">
                    <a href="{url controller=account action=payment sTarget=checkout}" class="button-middle small">
                        {s name="ConfirmLinkChangePayment" namespace="frontend/checkout/confirm_left"}{/s}
                    </a>
                </div>
            </div>
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name='frontend_checkout_confirm_agb_checkbox'}
    {if $sPayment.name=="PaymorrowRate" || $sPayment.name=="PaymorrowInvoice"}
        <script type="text/javascript">
            function toggle(e, show) {
                e.disabled = show ? false : true;
                e.style.opacity = show ? '1.0' : '0.4';
            }
            onload = function () {
                document.getElementById('basketButton_Paymorrow').style.opacity = '0.4';
                document.getElementById('basketButton_Paymorrow').disabled = true;
            }
        </script>
         <div class="agb_accept">
            {if !{config name='IgnoreAGB'}}
            <input onclick="toggle(document.getElementById('basketButton_Paymorrow'), this.checked)" type="checkbox"
               class="left" name="sAGB" id="sAGB" value="1"/>
            {/if}
            <label for="sAGB" class="chklabel modal_open {if $sAGBError}instyle_error{/if}">{s name="ConfirmTerms" namespace='frontend/checkout/confirm'}{/s}</label>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}

{/block}