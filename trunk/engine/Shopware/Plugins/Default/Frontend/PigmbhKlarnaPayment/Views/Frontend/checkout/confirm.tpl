{extends file="frontend/index/index.tpl"}

{block name='frontend_checkout_confirm_agb_checkbox'}
    {if $sPayment.name == "KlarnaInvoice" || $sPayment.name == "KlarnaPartPayment" && $piKlarnaCountryIso=="DE"}
        {if !{config name='IgnoreAGB'}}
            <div>
                <div class="agb_accept" style="top: 110px;">
                    <input type="checkbox" {if $agbChecked}checked="checked" {/if}class="left KlarnaInput" name="sAGB" id="sAGB" value="1" />
                    <label for="sAGB" class="chklabel modal_open{if $sAGBError} instyle_error{/if}">{s name="ConfirmTerms" namespace='frontend/checkout/confirm'}{/s}</label><br /><br />
                    <input type="checkbox" {if $standardAgbChecked}checked="checked" {/if} class="left KlarnaInput" name="klarnaAGB" id="klarnaAGB" value="1" />
                    <label for="klarnaAGB" class="chklabel Klarna_agb_acceptlabel{if $piKlarnaError} instyle_error{/if}" style="">
                        {$pi_Klarna_lang['klarnaagb']['start']}<a href="https://online.klarna.com/consent_de.yaws" class="Klarnacolor" target="_blank">{$pi_Klarna_lang['klarnaagb']['href']}</a> {$pi_Klarna_lang['klarnaagb']['end']} 
                    </label>
                </div>
                <div class="space" style="height: 40px;">&nbsp;</div>
            </div>
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_index_content_top"}
{if $sPayment.name == "KlarnaInvoice" || $sPayment.name == "KlarnaPartPayment"}
    <div class="grid_20 first">
        {* Step box *}
        {include file="frontend/register/steps.tpl" sStepActive="finished"}

        {* AGB is not accepted by user *}
        {if $sAGBError || $piKlarnaError}
            <div class="error agb_confirm">
                {if $sAGBError}    
                <div class="center">
                    <strong>
                        {s name='ConfirmErrorAGB' namespace='frontend/checkout/confirm'}{/s}
                    </strong>
                </div>
                {/if}    
                {if $piKlarnaError}    
                <div class="center">
                    <strong>
                        {$pi_Klarna_lang['agb']['error']}
                    </strong>
                </div>
                {/if}    
            </div>
        {/if}

        {* Check order headline *}
        {if $pi_klarna_viewport!='PiPaymentKlarna'}
            <div class="check_order">
                <h2 class="headingbox">{$pi_Klarna_lang['Payment_informations_header']}</h2>
                <div class="inner_container">
                    {* Payment informations *}
                    <p>{$pi_Klarna_lang['Payment_informations']}</p>
                </div>
            </div>
        {/if}
    </div>
{else}
    {$smarty.block.parent}
{/if}
{/block}

{block name='frontend_checkout_confirm_submit' append}
{if $sPayment.name=="KlarnaPartPayment" || $sPayment.name=="KlarnaInvoice"}
    <script type="text/javascript">
    document.getElementById("basketButton").style.display="none";
    function loading(){
            document.getElementById("basketButton_klarna").style.display="none";
            document.getElementById("klarna_loadingscreen").style.display="block";
            document.getElementById("klarnaactions").style.width="350px";
            document.getElementById("klarnaactions").style.height="40px";
        }
    </script>
    {* Submit order button *}
    <div class="actions" id="klarnaactions">
        <input type="submit" class="button-right large" id="basketButton_klarna" onclick="loading()" value="Zahlung durchf&uuml;hren" />
        <div id="klarna_loadingscreen" style="display:none; right:10px; bottom:20px; font-weight:bold;font-size:14px;">
            Die Bestellung wird an Klarna gesendet...&nbsp;<img style="top:10px; position:relative;" src="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhKlarnaPayment/img/ajax-loader.gif' fullPath}" />
        </div>
    </div>
{/if}
{/block}