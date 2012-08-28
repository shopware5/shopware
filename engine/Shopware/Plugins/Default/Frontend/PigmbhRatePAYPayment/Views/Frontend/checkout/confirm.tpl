{extends file='../_default/frontend/checkout/confirm.tpl'}
{block name='frontend_checkout_confirm_agb'}
{if $sPayment.name == "RatePAYInvoice" || $sPayment.name == "RatePAYRate" || $sPayment.name == "RatePAYDebit"}
    {if  $sPayment.name == "RatePAYRate"}
        <div id="ratepay_ratenrechner_div">
            <div id="ratepay_ratenrechner_inneres_div">
                <link type="text/css" rel="stylesheet" href="{link file='Ratenrechner/css/style.css' fullPath}"/>
                <script type="text/javascript">
                    pi_ratepay_rate_calc_path = "{link file='Ratenrechner/' fullPath}";
                    pi_ratepay_rate_ajax_path = "{url controller="RatepayPayment" action=""}";
                </script>
                <script type="text/javascript" src="{link file='Ratenrechner/js/mouseaction.js' fullPath}"></script>
                <script type="text/javascript" src="{link file='Ratenrechner/js/layout.js' fullPath}"></script>
                <script type="text/javascript" src="{link file='Ratenrechner/js/ajax.js' fullPath}"></script>
                <div id="pirpmain-cont" name="pirpratenrechnerContent"></div>
                <script type="text/javascript">
                    if(document.getElementById('pirpmain-cont')) {
                    piLoadrateCalculator();
                }
                </script>
            </div>
        </div>
    {/if}
    {if !{config name='IgnoreAGB'}}
        <div class="agb-holder" style='height: 70px;'>
        </div>
    {/if}
    <div class="clear"></div>
{else}
    {$smarty.block.parent}
{/if}
{/block}
{block name='frontend_checkout_confirm_agb_checkbox'}
{if $sPayment.name == "RatePAYInvoice" || $sPayment.name == "RatePAYRate" || $sPayment.name == "RatePAYDebit"}
    <div class="agb_accept">
        <div>
            <div class="agb_accept" style="position: inherit;">
                <input type="checkbox" class="left" name="sAGB" onclick="modifyField(this)" id="sAGB" value="1" />
                <label for="sAGB" class="modal_open {if $sAGBError}instyle_error{/if}">
                    {s name="ConfirmTerms" namespace='frontend/checkout/confirm'}{/s}</label><br />
                <label for="sAGB" class="ratepay_agb {if $sAGBError}instyle_error{/if}">
                    {if $piRatepayVars.wiederruf && $piRatepayVars.wiederruf != "http://" && $piRatepayVars.wiederruf != "https://"}
                        {s name="pigmbh_ratepay_agb_second" namespace="frontend/checkout/confirm.tpl"}Ich wurde &uuml;ber mein {/s}
                        <a href='{$piRatepayVars.wiederruf}' target="_blank" style="text-decoration:underline !important;">{s name="pigmbh_ratepay_agb_policy_wider" namespace="frontend/checkout/confirm.tpl"}Widerrufsrecht{/s}</a>
                        {s name="pigmbh_ratepay_agb_third" namespace="frontend/checkout/confirm.tpl"} informiert.{/s}
                        <br/>
                    {/if}
                    {s name="pigmbh_ratepay_agb_fourth" namespace="frontend/checkout/confirm.tpl"}Au&szlig;erdem erkl&auml;re ich hiermit meine Einwilligung zur Verwendung meiner Daten gem&auml;&szlig; der {/s}
                    <a href='{$piRatepayVars.ratepayDataText}' target="_blank" style="text-decoration:underline !important;">{s name="pigmbh_ratepay_agb_privatpolicy" namespace="frontend/checkout/confirm.tpl"}RatePAY-Datenschutzerkl&auml;rung{/s}</a>
                    {s name="pigmbh_ratepay_agb_fifth" namespace="frontend/checkout/confirm.tpl"} sowie der {/s}
                    <a href='{$piRatepayVars.merchantDataText}' target="_blank" style="text-decoration:underline !important;">{s name="pigmbh_ratepay_agb_ownerpolicy" namespace="frontend/checkout/confirm.tpl"}H&auml;ndler-Datenschutzerkl&auml;rung{/s}</a>
                    {s name="pigmbh_ratepay_agb_sixth" namespace="frontend/checkout/confirm.tpl"} und bin insbesondere damit einverstanden, zum Zwecke der Durchf&uuml;hrung des Vertrags &uuml;ber die von mir angegebene E-Mail-Adresse kontaktiert zu werden.{/s}
                </label>
            </div>
            <div class="space">&nbsp;</div>
        </div>
    </div>
{else}
    {$smarty.block.parent}
{/if}
{/block}
{block name="frontend_index_content_top"}
{if $sPayment.name == "RatePAYInvoice" || $sPayment.name == "RatePAYRate" || $sPayment.name == "RatePAYDebit"}
    <div class="grid_20 first">
        {* Step box *}
        {include file="frontend/register/steps.tpl" sStepActive="finished"}
        {if $sPaymentError!=false && $sAction!='finish'}
            <div class="error agb_confirm">
                <div class="center">
                    <strong>
                        {$sPaymentError}
                    </strong>
                </div>
            </div>
            {* AGB is not accepted by user *}
        {elseif $sAGBError && $Controller!='PiPaymentRatePAY' && $sAction!='finish'}
            <div class="error agb_confirm">
                <div class="center">
                    <strong>
                        {s name='ConfirmErrorAGB' namespace='frontend/checkout/confirm'}{/s}
                    </strong>
                </div>
            </div>
        {/if}
        {* Check order headline *}
        {if $Controller!='PiPaymentRatePAY' && $sAction!='finish'}
            <div class="check_order">
                <h2 class="headingbox">{s name="pigmbh_ratepay_paymentinfo_header" namespace="frontend/checkout/confirm.tpl"}Bitte &uuml;berpr&uuml;fen Sie Ihre Bestellung nochmals, bevor Sie sie senden.{/s}</h2>
                <div class="inner_container">
                    {* Payment informations *}
                    <p>
                        {if $sPayment.name=="RatePAYInvoice"}
                            <span id="pi_ratepay_shopname">{$sShopname}</span>
                            {s name="pigmbh_ratepay_paymentinfo_first" namespace="frontend/checkout/confirm.tpl"}<b>stellt mit der Unterst&uuml;tzung von RatePAY die M&ouml;glichkeit der RatePAY-Rechnung bereit. Sie nehmen damit einen Kauf auf Rechnung vor. Die Rechnung ist innerhalb von {/s}
                            {$piRatepayVars.dueDate}
                            {s name="pigmbh_ratepay_paymentinfo_second" namespace="frontend/checkout/confirm.tpl"}Tagen nach Rechnungsdatum zur Zahlung f&auml;llig.</b><br/><br/>{/s}
                            {s name="pigmbh_ratepay_paymentinfo_third" namespace="frontend/checkout/confirm.tpl"}RatePAY-Rechnung ist <b>ab einem Einkaufswert von</b>{/s}
                            <b>{$piRatepayVars.basketMin}</b>
                            {s name="pigmbh_ratepay_paymentinfo_fourth" namespace="frontend/checkout/confirm.tpl"}<b>&euro;</b> und <b>bis zu einem Einkaufswert von</b>{/s}
                            <b>{$piRatepayVars.basketMax}</b>
                            {s name="pigmbh_ratepay_paymentinfo_fifth" namespace="frontend/checkout/confirm.tpl"}<b>&euro;</b> m&ouml;glich (jeweils inklusive Mehrwertsteuer und Versandkosten).<br/><br/></b>{/s}
                            {s name="pigmbh_ratepay_paymentinfo_last" namespace="frontend/checkout/confirm.tpl"}Bitte beachten Sie, dass RatePAY-Rechnung nur genutzt werden kann, wenn Rechnungs- und Lieferaddresse identisch sind und Ihrem privaten Wohnort entsprechen. (keine Firmen- und keine Postfachadresse). Ihre Adresse muss im Gebiet der Bundesrepublik Deutschland liegen. Bitte korrigieren Sie gegebenenfalls Ihre Daten. {/s}
                        {elseif $sPayment.name=="RatePAYRate"}
                            {s name="pigmbh_ratepay_paymentinfo_rate_first" namespace="frontend/checkout/confirm.tpl"}<b>Mit RatePAY-Ratenzahlung w&auml;hlen Sie eine Bezahlung in Raten.</b><br/><br/>{/s}
                            {s name="pigmbh_ratepay_paymentinfo_rate_second" namespace="frontend/checkout/confirm.tpl"}RatePAY-Ratenzahlung kann <b>ab einem Einkaufswert von</b>{/s}
                            <b>{$piRatepayVars.basketMin}</b>
                            {s name="pigmbh_ratepay_paymentinfo_rate_third" namespace="frontend/checkout/confirm.tpl"}<b>&euro;</b> und <b>bis zu einem Einkaufswert von</b>{/s}
                            <b>{$piRatepayVars.basketMax}</b>
                            {s name="pigmbh_ratepay_paymentinfo_rate_fourth" namespace="frontend/checkout/confirm.tpl"}<b>&euro;</b> (jeweils inklusive Mehrwertsteuer und Versandkosten) genutzt werden.<br/><br/>{/s}
                            {s name="pigmbh_ratepay_paymentinfo_rate_fifth" namespace="frontend/checkout/confirm.tpl"}Ihre monatlichen Teilzahlungsrate, die Laufzeit der Teilzahlung und den entsprechenden Zinsaufschlag k&ouml;nnen Sie mit dem Ratenrechner im Anschluss ermitteln und festlegen.<br /><br />{/s}
                            {s name="pigmbh_ratepay_paymentinfo_rate_last" namespace="frontend/checkout/confirm.tpl"}Bitte beachten Sie, dass RatePAY-Rate nur genutzt werden kann, wenn Rechnungs- und Lieferaddresse identisch sind und Ihrem privaten Wohnort entsprechen. (keine Firmen- und keine Postfachadresse). Ihre Adresse muss im Gebiet der Bundesrepublik Deutschland liegen. Bitte gehen Sie korrigieren Sie gegebenenfalls Ihre Daten. {/s}
                        {elseif $sPayment.name=="RatePAYDebit"}
                            <span id="pi_ratepay_shopname">{$sShopname}</span>
                            {s name="pigmbh_ratepay_paymentinfo_debit_header" namespace="frontend/checkout/confirm.tpl"}<b>stellt mit der Unterst&uuml;tzung von RatePAY die M&ouml;glichkeit der RatePAY-Lastschrift bereit. Sie nehmen damit einen Kauf auf Lastschrift vor.</b><br/><br/>{/s}
                            {s name="pigmbh_ratepay_paymentinfo_debit_second" namespace="frontend/checkout/confirm.tpl"}RatePAY-Lastschrift ist <b>ab einem Einkaufswert von</b>{/s}
                            <b>{$piRatepayVars.basketMin}</b>
                            {s name="pigmbh_ratepay_paymentinfo_debit_third" namespace="frontend/checkout/confirm.tpl"}<b>&euro;</b> und <b>bis zu einem Einkaufswert von</b>{/s}
                            <b>{$piRatepayVars.basketMax}</b>
                            {s name="pigmbh_ratepay_paymentinfo_debit_fourth" namespace="frontend/checkout/confirm.tpl"}<b>&euro;</b> m&ouml;glich (jeweils inklusive Mehrwertsteuer und Versandkosten).<br/><br/>{/s}
                            {s name="pigmbh_ratepay_paymentinfo_debit_last" namespace="frontend/checkout/confirm.tpl"}Bitte beachten Sie, dass RatePAY-Lastschrift nur genutzt werden kann, wenn Rechnungs- und Lieferaddresse identisch sind und Ihrem privaten Wohnort entsprechen. (keine Firmen- und keine Postfachadresse). Ihre Adresse muss im Gebiet der Bundesrepublik Deutschland liegen. Bitte korrigieren Sie gegebenenfalls Ihre Daten. {/s}
                        {/if}
                    </p>
                    <p>
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
{if $sPayment.name=="RatePAYRate"}

    <script type="text/javascript">
    document.getElementById("basketButton").style.display="none";
    function loading(){
    document.getElementById("basketButton_ratepay").style.display="none";
    document.getElementById("ratepay_loadingscreen").style.display="block";
    document.getElementById("ratepayactions").style.width="350px";
    document.getElementById("ratepayactions").style.height="40px";
}
function modifyField(checkbox){
if(checkbox.checked){
if(!document.getElementById("piInstallmentTerms") || document.getElementById("pirperror")){
window.scrollBy(0, -600);
alert("{s name="pigmbh_ratepay_paymentinfo_rate_ratecalculatorwarning" namespace="frontend/checkout/confirm.tpl"}Bitte lassen Sie sich Ihren Ratenplan berechnen{/s}");
document.getElementById('rate').focus();
document.getElementById('sAGB').checked=false;
}
else{
document.getElementById("basketButton_ratepay").style.opacity="1";
document.getElementById('basketButton_ratepay').disabled = false;
}
}
else{
document.getElementById("basketButton_ratepay").style.opacity="0.5";
document.getElementById('basketButton_ratepay').disabled = true;
}
}
    </script>
    {* Submit order button *}
    <div class="actions" id="ratepayactions">
        <input type="submit" class="button-right large" id="basketButton_ratepay" onclick="loading()" value="Zahlung durchf&uuml;hren" />
        <div id="ratepay_loadingscreen" style="display:none; right:10px; bottom:20px; font-weight:bold;font-size:14px;">
            {s name="pigmbh_ratepay_agb_loadingtext" namespace="frontend/checkout/confirm.tpl"}Die Bestellung wird an RatePAY gesendet...&nbsp;{/s}<img style="top:10px; position:relative;" src="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/ajax-loader.gif' fullPath}" />
        </div>
    </div>
    <script type="text/javascript">
document.getElementById("basketButton_ratepay").style.opacity="0.5";
    </script>
{elseif $sPayment.name=="RatePAYInvoice" || $sPayment.name == "RatePAYDebit"}
    <script type="text/javascript">
document.getElementById("basketButton").style.display="none";
function loading(){
document.getElementById("basketButton_ratepay").style.display="none";
document.getElementById("ratepay_loadingscreen").style.display="block";
document.getElementById("ratepayactions").style.width="350px";
document.getElementById("ratepayactions").style.height="40px";
}
function modifyField(checkbox){
if(checkbox.checked){
document.getElementById("basketButton_ratepay").style.opacity="1";
document.getElementById('basketButton_ratepay').disabled = false;
}
else{
document.getElementById("basketButton_ratepay").style.opacity="0.5";
document.getElementById('basketButton_ratepay').disabled = true;
}
}
    </script>
    {* Submit order button *}
    <div class="actions" id="ratepayactions">
        <input type="submit" class="button-right large" id="basketButton_ratepay" onclick="loading()" value="Zahlung durchf&uuml;hren" />
        <div id="ratepay_loadingscreen">
            {s name="pigmbh_ratepay_agb_loadingtext" namespace="frontend/checkout/confirm.tpl"}Die Bestellung wird an RatePAY gesendet...&nbsp;{/s}<img style="top:10px; position:relative;" src="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/ajax-loader.gif' fullPath}" />
        </div>
    </div>
    <script type="text/javascript">
document.getElementById("basketButton_ratepay").style.opacity="0.5";
    </script>
{/if}
{/block}

