{* Javascript *}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
    //<![CDATA[
    jQuery(document).ready(function($) {
    $("#paymentFieldsetDebitSelect").change(function () {
    if($("#paymentFieldsetDebitSelect").val()=="directDebit"){
    $("#ratepayDirectDebit").slideDown();
    $("#debitWarning").slideDown();
    $("#debitWarningRegister").slideDown();
} else{
$("#ratepayDirectDebit").slideUp();
$("#debitWarning").slideUp();
$("#debitWarningRegister").slideUp();
}
}).change();
});
//]]>
</script>
{/block}
{if $Controller == "account" || $Controller == "register"}
    {block name='frontend_register_payment_fieldset_description'}
    {if $payment_mean.name == "RatePAYInvoice"}
        <div class="grid_10 last" id="ratepay_width_register">
            {$payment_mean.additionaldescription}
            {if $pi_ratepay_b2b_invoice == false}
            <div class="grid_8" id="ratepay_invoicebirth" style="float: right; text-align: right">
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday'])}
                    <div id="birthdate" class="Ratepay_birthday_div">
                    <table style="border: 0px; margin: 0px; float: right;">
                        <tr>
                            <td>
                                <label for="register_personal_birthdate" class="Ratepay_birthday_label" style="width: 150px;">{s name="pigmbh_ratepay_birthday_text" namespace="Frontend/register/payment_fielset"}Geburtstag(TT/MM/JJJJ)*{/s}</label>
                            </td>
                            <td>
                                <select id="register_personal_birthdate" name="registerRatePAY[personal][birthday_invoice]">
                                    <option value="">--</option>
                                    {section name="birthdate" start=1 loop=32 step=1}
                                        <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                                    {/section}
                                </select>
                                <select name="registerRatePAY[personal][birthmonth_invoice]">
                                    <option value="">-</option>
                                    {section name="birthmonth" start=1 loop=13 step=1}
                                        <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                                    {/section}
                                </select>
                                <select name="registerRatePAY[personal][birthyear_invoice]">
                                    <option value="">----</option>
                                    {section name="birthyear" loop=2000 max=100 step=-1}
                                        <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                                    {/section}
                                </select>
                            </td>
                        </tr>
                    </table>
                    </div>
                {/if}
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Ratepay_phone_div">
                        <label for="register_personal_phone" class="Ratepay_phone_label"><br />{s name="pigmbh_ratepay_phone_text" namespace="Frontend/register/payment_fielset"}Telefon/Handynummer*{/s}</label>
                        <input id="register_personal_phone" class="text pi_ratepay_phone" type="text" value="" name="registerRatePAY[personal][phone_invoice]">
                    </div>
                {/if}
                {if $pi_ratepay_company}
                    <div id="company" class="Ratepay_company_div">
                        <label for="register_personal_company" class="Ratepay_company_label"><br />{s name="pigmbh_ratepay_company_text" namespace="Frontend/register/payment_fielset"}Firmenname*{/s}</label>
                        <input id="register_personal_company" class="text pi_ratepay_company" type="text" value="" name="registerRatePAY[personal][company_invoice]">
                    </div>
                {elseif $pi_ratepay_ustid}
                    <div id="company" class="Ratepay_ustid_div">
                        <label for="register_personal_ustid" class="Ratepay_ustid_label"><br />{s name="pigmbh_ratepay_ustid_text" namespace="Frontend/register/payment_fielset"}Umsatzsteuer ID*{/s}</label>
                        <input id="register_personal_ustid" class="text pi_ratepay_ustid" type="text" value="" name="registerRatePAY[personal][ustid_invoice]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_ustid || $pi_ratepay_company}
                    <input class="pi_ratePAY_savebutton" name="saveRatepayInvoiceData" type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
                {/if}
            </div>
            {/if}
        </div>
    {elseif $payment_mean.name == "RatePAYRate"}
        <div class="grid_10 last" id="ratepay_width_register">
            {$payment_mean.additionaldescription}
            {if $pi_ratepay_b2b_rate == false}
            <div class="grid_8" id="ratepay_invoicebirth" style="float: right; text-align: right">
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday'])}
                    <div id="birthdate" class="Ratepay_birthday_div">
                    <table style="border: 0px; margin: 0px;  float: right;">
                        <tr>
                            <td>
                                <label for="register_personal_birthdate" class="Ratepay_birthday_label" style="width: 150px;">{s name="pigmbh_ratepay_birthday_text" namespace="Frontend/register/payment_fielset"}Geburtstag(TT/MM/JJJJ)*{/s}</label>
                            </td>
                            <td>
                                <select id="register_personal_birthdate" name="registerRatePAY[personal][birthday_rate]">
                                    <option value="">--</option>
                                    {section name="birthdate" start=1 loop=32 step=1}
                                        <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthmonth_rate]">
                                    <option value="">-</option>
                                    {section name="birthmonth" start=1 loop=13 step=1}
                                        <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthyear_rate]">
                                    <option value="">----</option>
                                    {section name="birthyear" loop=2000 max=100 step=-1}
                                        <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                                    {/section}
                                </select>
                            </td>
                        </tr>
                    </table>
                    </div>
                {/if}
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Ratepay_phone_div">
                        <label for="register_personal_phone" class="Ratepay_phone_label"><br />{s name="pigmbh_ratepay_phone_text" namespace="Frontend/register/payment_fielset"}Telefon/Handynummer*{/s}</label>
                        <input id="register_personal_phone" class="text pi_ratepay_phone" type="text" value="" name="registerRatePAY[personal][phone_rate]">
                    </div>
                {/if}
                {if $pi_ratepay_company}
                    <div id="company" class="Ratepay_company_div">
                        <label for="register_personal_company" class="Ratepay_company_label"><br />{s name="pigmbh_ratepay_company_text" namespace="Frontend/register/payment_fielset"}Firmenname*{/s}</label>
                        <input id="register_personal_company" class="text pi_ratepay_company" type="text" value="" name="registerRatePAY[personal][company_rate]">
                    </div>
                {elseif $pi_ratepay_ustid}
                    <div id="company" class="Ratepay_ustid_div">
                        <label for="register_personal_ustid" class="Ratepay_ustid_label"><br />{s name="pigmbh_ratepay_ustid_text" namespace="Frontend/register/payment_fielset"}Umsatzsteuer ID*{/s}</label>
                        <input id="register_personal_ustid" class="text pi_ratepay_ustid" type="text" value="" name="registerRatePAY[personal][ustid_rate]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_ustid || $pi_ratepay_company}
                    <input class="pi_ratePAY_savebutton" name="saveRatepayRateData" type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
                {/if}
            </div>
            {/if}
        </div>
    {elseif $payment_mean.name == "RatePAYDebit"}
        <div class="grid_10 last" id="ratepay_width_register">
            {$payment_mean.additionaldescription}
            {if $pi_ratepay_b2b_debit == false}
            <div class="grid_8" id="ratepay_invoicebirth" style="float: right; text-align: right">
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday'])}
                    <div id="birthdate" class="Ratepay_birthday_div">
                    <table style="border: 0px; margin: 0px;  float: right;">
                        <tr>
                            <td>
                                <label for="register_personal_birthdate" class="Ratepay_birthday_label" style="width: 150px;">{s name="pigmbh_ratepay_birthday_text" namespace="Frontend/register/payment_fielset"}Geburtstag(TT/MM/JJJJ)*{/s}</label>
                            </td>
                            <td>
                                <select id="register_personal_birthdate" name="registerRatePAY[personal][birthday_debit]">
                                    <option value="">--</option>
                                    {section name="birthdate" start=1 loop=32 step=1}
                                        <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthmonth_debit]">
                                    <option value="">-</option>
                                    {section name="birthmonth" start=1 loop=13 step=1}
                                        <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthyear_debit]">
                                    <option value="">----</option>
                                    {section name="birthyear" loop=2000 max=100 step=-1}
                                        <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                                    {/section}
                                </select>
                            </td>
                        </tr>
                    </table>
                    </div>
                {/if}
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Ratepay_phone_div">
                        <label for="register_personal_phone" class="Ratepay_phone_label"><br />{s name="pigmbh_ratepay_phone_text" namespace="Frontend/register/payment_fielset"}Telefon/Handynummer*{/s}</label>
                        <input id="register_personal_phone" class="text pi_ratepay_phone" type="text" value="" name="registerRatePAY[personal][phone_debit]">
                    </div>
                {/if}
                {if $pi_ratepay_company}
                    <div id="company" class="Ratepay_company_div">
                        <label for="register_personal_company" class="Ratepay_company_label"><br />{s name="pigmbh_ratepay_company_text" namespace="Frontend/register/payment_fielset"}Firmenname*{/s}</label>
                        <input id="register_personal_company" class="text pi_ratepay_company" type="text" value="" name="registerRatePAY[personal][company_debit]">
                    </div>
                {elseif $pi_ratepay_ustid}
                    <div id="company" class="Ratepay_ustid_div">
                        <label for="register_personal_ustid" class="Ratepay_ustid_label"><br />{s name="pigmbh_ratepay_ustid_text" namespace="Frontend/register/payment_fielset"}Umsatzsteuer ID*{/s}</label>
                        <input id="register_personal_ustid" class="text pi_ratepay_ustid" type="text" value="" name="registerRatePAY[personal][ustid_debit]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_ustid || $pi_ratepay_company}
                    <input class="pi_ratePAY_savebutton" name="saveRatepayDebitData" type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
                {/if}
            </div>
            {/if}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
    {/block}

    {block name='frontend_register_payment_fieldset_input_radio'}
    {if ( $pi_ratepay_no_ratepay || $sUserData.billingaddress.birthday=="0000-00-00" || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_company || $pi_ratepay_ustid || $pi_ratepay_toyoung || $pi_ratepay_address || $pi_ratepay_b2b_invoice) && ($payment_mean.name == "RatePAYInvoice")}
        <div id="pi_RatePAY_paymentWarning_register"><center>
                {if $piRatepayInvoiceWarning == 'b2b'}
                {s name="pigmbh_ratepay_warning_invoice_b2b" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Rechnung nur als Privatkunde m&ouml;glich.{/s}
                {elseif $piRatepayInvoiceWarning == 'all_company'}
                {s name="pigmbh_ratepay_warning_invoice_all_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und den Firmennamen ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'all_ustid'}
                {s name="pigmbh_ratepay_warning_invoice_all_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und die Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'both'}
                {s name="pigmbh_ratepay_warning_invoice_both" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag und Ihre Telefonnummer ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'birthday_company'}
                {s name="pigmbh_ratepay_warning_invoice_birthday_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihren Firmennamen ein{/s}
                {elseif $piRatepayInvoiceWarning == 'birthday_ustid'}
                {s name="pigmbh_ratepay_warning_invoice_birthday_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'birthday'}
                {s name="pigmbh_ratepay_warning_invoice_birthday" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'phone_ustid'}
                {s name="pigmbh_ratepay_warning_invoice_phone_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'phone_company'}
                {s name="pigmbh_ratepay_warning_invoice_phone_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und den Firmenname ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'phone'}
                {s name="pigmbh_ratepay_warning_invoice_phone" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer ein{/s}
                {elseif $piRatepayInvoiceWarning == 'company'}
                {s name="pigmbh_ratepay_warning_invoice_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Firmennamen ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'ustid'}
                {s name="pigmbh_ratepay_warning_invoice_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'address'}
                {s name="pigmbh_ratepay_warning_invoice_address" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren m&uuml;ssen Rechnungs- und Lieferaddresse identisch sein{/s}
                {elseif $piRatepayInvoiceWarning == 'toyoung'}
                {s name="pigmbh_ratepay_warning_invoice_toyoung" namespace="frontend/register/payment_fieldset.tpl"}Bitte beachten Sie, dass RatePAY Rechnung erst ab einem Alter von 18 Jahren genutzt werden kann.{/s}
                {elseif $piRatepayInvoiceWarning == 'notaccepted'}
                {s name="pigmbh_ratepay_warning_invoice_notaccepted" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Rechnung nicht m&ouml;glich. Diese Entscheidung ist von RatePAY auf der Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten erfahren Sie in der{/s}
                <label class="RatePAYAgbLabel"><a target="_blank" href="{$datenschutzRatepayInvoice}">{s name="pigmbh_ratepay_warning_invoice_notaccepted_href" namespace="frontend/register/payment_fieldset.tpl"}RatePAY-Datenschutzerkl&auml;rung.{/s}</a></label>
                {/if}
            </center></div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayInvoiceSurcharge}
                <span style="margin-left: 27px; float: left;">
                    ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayInvoiceSurcharge})
                </span>
            {/if}
        </div>

    {elseif ( $pi_ratepay_no_ratepay || $sUserData.billingaddress.birthday=="0000-00-00" || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_company || $pi_ratepay_ustid || $pi_ratepay_toyoung || $pi_ratepay_address || $pi_ratepay_b2b_rate) && ($payment_mean.name == "RatePAYRate")}
        <div id="pi_RatePAY_paymentWarning_register"><center>
                {if $piRatepayRateWarning == 'b2b'}
                {s name="pigmbh_ratepay_warning_rate_b2b" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY-Ratenzahlung nur als Privatkunde m&ouml;glich.{/s}
                {elseif $piRatepayRateWarning == 'all_company'}
                {s name="pigmbh_ratepay_warning_rate_all_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und den Firmennamen ein.{/s}
                {elseif $piRatepayRateWarning == 'all_ustid'}
                {s name="pigmbh_ratepay_warning_rate_all_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und die Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayRateWarning == 'both'}
                {s name="pigmbh_ratepay_warning_rate_both" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag und Ihre Telefonnummer ein.{/s}
                {elseif $piRatepayRateWarning == 'birthday_company'}
                {s name="pigmbh_ratepay_warning_rate_birthday_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihren Firmennamen ein{/s}
                {elseif $piRatepayRateWarning == 'birthday_ustid'}
                {s name="pigmbh_ratepay_warning_rate_birthday_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayRateWarning == 'birthday'}
                {s name="pigmbh_ratepay_warning_rate_birthday" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum ein.{/s}
                {elseif $piRatepayRateWarning == 'phone_ustid'}
                {s name="pigmbh_ratepay_warning_rate_phone_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayRateWarning == 'phone_company'}
                {s name="pigmbh_ratepay_warning_rate_phone_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und den Firmenname ein.{/s}
                {elseif $piRatepayRateWarning == 'phone'}
                {s name="pigmbh_ratepay_warning_rate_phone" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer ein{/s}
                {elseif $piRatepayRateWarning == 'company'}
                {s name="pigmbh_ratepay_warning_rate_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihren Firmennamen ein.{/s}
                {elseif $piRatepayRateWarning == 'ustid'}
                {s name="pigmbh_ratepay_warning_rate_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihre Umasatzsteuer ID ein.{/s}
                {elseif $piRatepayRateWarning == 'address'}
                {s name="pigmbh_ratepay_warning_rate_address" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren m&uuml;ssen Rechnungs- und Lieferaddresse identisch sein{/s}
                {elseif $piRatepayRateWarning == 'toyoung'}
                {s name="pigmbh_ratepay_warning_rate_toyoung" namespace="frontend/register/payment_fieldset.tpl"}Bitte beachten Sie, dass RatePAY Ratenzahlung erst ab einem Alter von 18 Jahren genutzt werden kann.{/s}
                {elseif $piRatepayRateWarning == 'notaccepted'}
                {s name="pigmbh_ratepay_warning_rate_notaccepted" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Ratenzahlung nicht m&ouml;glich. Diese Entscheidung ist von RatePAY auf der Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten erfahren Sie in der{/s}
                <label class="RatePAYAgbLabel"><a target="_blank" href="{$datenschutzRatepayRate}">{s name="pigmbh_ratepay_warning_rate_notaccepted_href" namespace="frontend/register/payment_fieldset.tpl"}RatePAY-Datenschutzerkl&auml;rung.{/s}</a></label>
                {/if}
        </center></div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayRateSurcharge}
                <span style="margin-left: 27px; float: left;">
                    ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayRateSurcharge})
                </span>
            {/if}
            {if $activateDebit == 1}
                <span style="margin-left: 27px; float: left;">
                    <select id="paymentFieldsetDebitSelect" name="registerRatePAY[personal][debitPayment]"  disabled="disabled">
                        <option value="bankTransfer">{s name="pigmbh_ratepay_debit_bankTransfer" namespace="frontend/register/payment_fieldset.tpl"}Per &Uuml;berweisung{/s}</option>
                        <option value="directDebit">{s name="pigmbh_ratepay_debit_directDebit" namespace="frontend/register/payment_fieldset.tpl"}Per elektronischem Lastschriftverfahren{/s}</option>
                    </select>
                </span>
            {elseif $payment_mean.name == "RatePAYRate" && !$activateDebit}
                <span style="margin-left: 27px; float: left;">
                    <select name="registerRatePAY[personal][debitPayment]" id="paymentFieldsetDebitSelect" style="display:none;">
                        <option value="bankTransfer" {if !$ratepayDebitPayType}selected{/if}>{s name="pigmbh_ratepay_debit_bankTransfer" namespace="frontend/register/payment_fieldset.tpl"}Per &Uuml;berweisung{/s}</option>
                    </select>
                </span>
            {/if}
        </div>
    {elseif ( $pi_ratepay_no_ratepay || $sUserData.billingaddress.birthday=="0000-00-00" || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_company || $pi_ratepay_ustid || $pi_ratepay_toyoung || $pi_ratepay_address || $pi_ratepay_b2b_debit) && ($payment_mean.name == "RatePAYDebit")}
        <div id="pi_RatePAY_paymentWarning_register"><center>
                {if $piRatepayDebitWarning == 'b2b'}
                {s name="pigmbh_ratepay_warning_debit_b2b" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Lastschrift nur als Privatkunde m&ouml;glich.{/s}
                {elseif $piRatepayDebitWarning == 'all_company'}
                {s name="pigmbh_ratepay_warning_debit_all_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und den Firmennamen ein.{/s}
                {elseif $piRatepayDebitWarning == 'all_ustid'}
                {s name="pigmbh_ratepay_warning_debit_all_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und die Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayDebitWarning == 'both'}
                {s name="pigmbh_ratepay_warning_debit_both" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag und Ihre Telefonnummer ein.{/s}
                {elseif $piRatepayDebitWarning == 'birthday_company'}
                {s name="pigmbh_ratepay_warning_debit_birthday_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihren Firmennamen ein{/s}
                {elseif $piRatepayDebitWarning == 'birthday_ustid'}
                {s name="pigmbh_ratepay_warning_debit_birthday_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayDebitWarning == 'birthday'}
                {s name="pigmbh_ratepay_warning_debit_birthday" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum ein.{/s}
                {elseif $piRatepayDebitWarning == 'phone_ustid'}
                {s name="pigmbh_ratepay_warning_debit_phone_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayDebitWarning == 'phone_company'}
                {s name="pigmbh_ratepay_warning_debit_phone_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und den Firmenname ein.{/s}
                {elseif $piRatepayDebitWarning == 'phone'}
                {s name="pigmbh_ratepay_warning_debit_phone" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer ein{/s}
                {elseif $piRatepayDebitWarning == 'company'}
                {s name="pigmbh_ratepay_warning_debit_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihren Firmennamen ein.{/s}
                {elseif $piRatepayDebitWarning == 'ustid'}
                {s name="pigmbh_ratepay_warning_debit_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihre Umasatzsteuer ID ein.{/s}
                {elseif $piRatepayDebitWarning == 'address'}
                {s name="pigmbh_ratepay_warning_debit_address" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren m&uuml;ssen Rechnungs- und Lieferaddresse identisch sein{/s}
                {elseif $piRatepayDebitWarning == 'toyoung'}
                {s name="pigmbh_ratepay_warning_debit_toyoung" namespace="frontend/register/payment_fieldset.tpl"}Bitte beachten Sie, dass RatePAY Lastschrift erst ab einem Alter von 18 Jahren genutzt werden kann.{/s}
                {elseif $piRatepayDebitWarning == 'notaccepted'}
                {s name="pigmbh_ratepay_warning_debit_notaccepted" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Lastschrift nicht m&ouml;glich. Diese Entscheidung ist von RatePAY auf der Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten erfahren Sie in der{/s}
                <label class="RatePAYAgbLabel"><a target="_blank" href="{$datenschutzRatepayDebit}">{s name="pigmbh_ratepay_warning_debit_notaccepted_href" namespace="frontend/register/payment_fieldset.tpl"}RatePAY-Datenschutzerkl&auml;rung.{/s}</a></label>
                {/if}</center></div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayRateSurcharge}
                <span style="margin-left: 27px; float: left;">
                    ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayRateSurcharge})
                </span>
            {/if}
        </div>
    {elseif $payment_mean.name == "RatePAYDebit" && (!$debitData.owner || !$debitData.accountnumber || !$debitData.bankcode || !$debitData.bankname)}
        <div id="directDebitWarningRegisterTwo"><center>{s name="pigmbh_ratepay_debit_warning" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, f&uuml;llen Sie bitte alle Felder aus{/s}</center></div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $form_data.payment or (!$form_data && !$smarty.foreach.register_payment_mean.index)} checked="checked"{/if}/>
            <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayDebitSurcharge}
                <span style="margin-left: 27px; float: left;">
                    ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayDebitSurcharge})
                </span>
            {/if}
        </div>
    {elseif $payment_mean.name == "RatePAYDebit" || $payment_mean.name == "RatePAYRate" || $payment_mean.name == "RatePAYInvoice"}
        {if $payment_mean.name == "RatePAYRate" && $activateDebit && (!$debitData.owner || !$debitData.accountnumber || !$debitData.bankcode || !$debitData.bankname)}
            <div id="debitWarningRegister"><center>{s name="pigmbh_ratepay_debit_debitwarning" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung mit RatePAY Rate per elektronischer Lastschrift durchzuf&uuml;hren, f&uuml;llen Sie bitte alle Felder aus{/s}</center></div>
        {/if}

        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $form_data.payment or (!$form_data && !$smarty.foreach.register_payment_mean.index)} checked="checked"{/if} />
            <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayDebitSurcharge || $ratepayRateSurcharge || $ratepayInvoiceSurcharge}
                <span style="margin-left: 27px; float: left;">
                    {if $payment_mean.name == "RatePAYDebit" && $ratepayDebitSurcharge}
                        ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayDebitSurcharge})
                    {elseif $payment_mean.name == "RatePAYRate" && $ratepayRateSurcharge}
                        ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayRateSurcharge})
                    {elseif $payment_mean.name == "RatePAYInvoice" && $ratepayInvoiceSurcharge}
                        ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayInvoiceSurcharge})
                    {/if}
                </span>
            {/if}
            {if $payment_mean.name == "RatePAYRate" && $activateDebit}
                <span style="margin-left: 27px; float: left;">
                    <select name="registerRatePAY[personal][debitPayment]" id="paymentFieldsetDebitSelect">
                        <option value="bankTransfer"{if !$ratepayDebitPayType}selected{/if}>{s name="pigmbh_ratepay_debit_bankTransfer" namespace="frontend/register/payment_fieldset.tpl"}Per &Uuml;berweisung{/s}</option>
                        <option value="directDebit"{if $ratepayDebitPayType}selected{/if}>{s name="pigmbh_ratepay_debit_directDebit" namespace="frontend/register/payment_fieldset.tpl"}Per elektronischem Lastschriftverfahren{/s}</option>
                    </select>
                </span>
            {elseif $payment_mean.name == "RatePAYRate" && !$activateDebit}
                <span style="margin-left: 27px; float: left;">
                    <select name="registerRatePAY[personal][debitPayment]" id="paymentFieldsetDebitSelect" style="display:none;">
                        <option value="bankTransfer" {if !$ratepayDebitPayType}selected{/if}>{s name="pigmbh_ratepay_debit_bankTransfer" namespace="frontend/register/payment_fieldset.tpl"}Per &Uuml;berweisung{/s}</option>
                    </select>
                </span>
            {/if}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
    {/block}

    {block name='frontend_register_error_messages'}
    {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone']}
        {if $pi_ratepay_PaymentError!=false}
            <div class="error agb_confirm" style="margin:0">
                <div class="center">
                    <strong>
                        {if $piRatepayInvoiceWarning == 'b2b'}
                        {s name="pigmbh_ratepay_warning_invoice_b2b" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Rechnung nur als Privatkunde m&ouml;glich.{/s}
                        {elseif $piRatepayInvoiceWarning == 'all_company'}
                        {s name="pigmbh_ratepay_warning_invoice_all_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und den Firmennamen ein.{/s}
                        {elseif $piRatepayInvoiceWarning == 'all_ustid'}
                        {s name="pigmbh_ratepay_warning_invoice_all_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und die Umsatzsteuer ID ein.{/s}
                        {elseif $piRatepayInvoiceWarning == 'both'}
                        {s name="pigmbh_ratepay_warning_invoice_both" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag und Ihre Telefonnummer ein.{/s}
                        {elseif $piRatepayInvoiceWarning == 'birthday_company'}
                        {s name="pigmbh_ratepay_warning_invoice_birthday_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihren Firmennamen ein{/s}
                        {elseif $piRatepayInvoiceWarning == 'birthday_ustid'}
                        {s name="pigmbh_ratepay_warning_invoice_birthday_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihre Umsatzsteuer ID ein.{/s}
                        {elseif $piRatepayInvoiceWarning == 'birthday'}
                        {s name="pigmbh_ratepay_warning_invoice_birthday" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum ein.{/s}
                        {elseif $piRatepayInvoiceWarning == 'phone_ustid'}
                        {s name="pigmbh_ratepay_warning_invoice_phone_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und Ihre Umsatzsteuer ID ein.{/s}
                        {elseif $piRatepayInvoiceWarning == 'phone_company'}
                        {s name="pigmbh_ratepay_warning_invoice_phone_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und den Firmenname ein.{/s}
                        {elseif $piRatepayInvoiceWarning == 'phone'}
                        {s name="pigmbh_ratepay_warning_invoice_phone" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer ein{/s}
                        {elseif $piRatepayInvoiceWarning == 'company'}
                        {s name="pigmbh_ratepay_warning_invoice_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Firmennamen ein.{/s}
                        {elseif $piRatepayInvoiceWarning == 'ustid'}
                        {s name="pigmbh_ratepay_warning_invoice_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Umsatzsteuer ID ein.{/s}
                        {elseif $piRatepayInvoiceWarning == 'address'}
                        {s name="pigmbh_ratepay_warning_invoice_address" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren m&uuml;ssen Rechnungs- und Lieferaddresse identisch sein{/s}
                        {elseif $piRatepayInvoiceWarning == 'toyoung'}
                        {s name="pigmbh_ratepay_warning_invoice_toyoung" namespace="frontend/register/payment_fieldset.tpl"}Bitte beachten Sie, dass RatePAY Rechnung erst ab einem Alter von 18 Jahren genutzt werden kann.{/s}
                        {elseif $piRatepayInvoiceWarning == 'notaccepted'}
                        {s name="pigmbh_ratepay_warning_invoice_notaccepted" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Rechnung nicht m&ouml;glich. Diese Entscheidung ist von RatePAY auf der Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten erfahren Sie in der{/s}
                        <label class="RatePAYAgbLabel"><a target="_blank" href="{$datenschutzRatepayInvoice}">{s name="pigmbh_ratepay_warning_invoice_notaccepted_href" namespace="frontend/register/payment_fieldset.tpl"}RatePAY-Datenschutzerkl&auml;rung.{/s}</a></label>
                        {/if}
                    </strong>
                </div>
            </div>
        {else}
            {$smarty.block.parent}
        {/if}
    {/if}
    {/block}
{elseif $Controller == "checkout"}
    {block name='frontend_checkout_payment_fieldset_description'}
    {if $payment_mean.name == "RatePAYInvoice"}
        <div class="grid_10 last" id="ratepay_width_checkout">
            {$payment_mean.additionaldescription}
            {if $pi_ratepay_b2b_invoice == false}
            <div class="grid_8" id="ratepay_invoicebirth" style="float: right; text-align: right">
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday'])}
                    <div id="birthdate" class="Ratepay_birthday_div">
                    <table style="border: 0px; margin: 0px;  float: right;">
                        <tr>
                            <td>
                                <label for="register_personal_birthdate" class="Ratepay_birthday_label" style="width: 150px">{s name="pigmbh_ratepay_birthday_text" namespace="Frontend/register/payment_fielset"}Geburtstag(TT/MM/JJJJ)*{/s}</label>
                            </td>
                            <td>
                                <select id="register_personal_birthdate" name="registerRatePAY[personal][birthday_invoice]">
                                    <option value="">--</option>
                                    {section name="birthdate" start=1 loop=32 step=1}
                                        <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthmonth_invoice]">
                                    <option value="">-</option>
                                    {section name="birthmonth" start=1 loop=13 step=1}
                                        <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthyear_invoice]">
                                    <option value="">----</option>
                                    {section name="birthyear" loop=2000 max=100 step=-1}
                                        <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                                    {/section}
                                </select>
                            </td>
                        </tr>
                    </table>
                    </div>
                {/if}
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Ratepay_phone_div">
                        <label for="register_personal_phone" class="Ratepay_phone_label"><br />{s name="pigmbh_ratepay_phone_text" namespace="Frontend/register/payment_fielset"}Telefon/Handynummer*{/s}</label>
                        <input id="register_personal_phone" class="text pi_ratepay_phone" type="text" value="" name="registerRatePAY[personal][phone_invoice]">
                    </div>
                {/if}
                {if $pi_ratepay_company}
                    <div id="company" class="Ratepay_company_div">
                        <label for="register_personal_company" class="Ratepay_company_label"><br />{s name="pigmbh_ratepay_company_text" namespace="Frontend/register/payment_fielset"}Firmenname*{/s}</label>
                        <input id="register_personal_company" class="text pi_ratepay_company" type="text" value="" name="registerRatePAY[personal][company_invoice]">
                    </div>
                {elseif $pi_ratepay_ustid}
                    <div id="company" class="Ratepay_ustid_div">
                        <label for="register_personal_ustid" class="Ratepay_ustid_label"><br />{s name="pigmbh_ratepay_ustid_text" namespace="Frontend/register/payment_fielset"}Umsatzsteuer ID*{/s}</label>
                        <input id="register_personal_ustid" class="text pi_ratepay_ustid" type="text" value="" name="registerRatePAY[personal][ustid_invoice]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_ustid || $pi_ratepay_company}
                    <input class="pi_ratePAY_savebutton_checkout" name="saveRatepayInvoiceData" type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
                {/if}
            </div>
            {/if}
        </div>
    {elseif  $payment_mean.name == "RatePAYRate"}
        <div class="grid_10 last" id="ratepay_width_checkout">
            {$payment_mean.additionaldescription}
            {if $pi_ratepay_b2b_rate == false}
            <div class="grid_8" id="ratepay_invoicebirth" style="float: right; text-align: right">
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday'])}
                    <div id="birthdate" class="Ratepay_birthday_div">
                    <table style="border: 0px; margin: 0px;  float: right;">
                        <tr>
                            <td>
                                <label for="register_personal_birthdate" class="Ratepay_birthday_label" style="width: 150px">{s name="pigmbh_ratepay_birthday_text" namespace="Frontend/register/payment_fielset"}Geburtstag(TT/MM/JJJJ)*{/s}</label>
                            </td>
                            <td>
                                <select id="register_personal_birthdate" name="registerRatePAY[personal][birthday_rate]">
                                    <option value="">--</option>
                                    {section name="birthdate" start=1 loop=32 step=1}
                                        <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthmonth_rate]">
                                    <option value="">-</option>
                                    {section name="birthmonth" start=1 loop=13 step=1}
                                        <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthyear_rate]">
                                    <option value="">----</option>
                                    {section name="birthyear" loop=2000 max=100 step=-1}
                                        <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                                    {/section}
                                </select>
                            </td>
                        </tr>
                    </table>
                    </div>
                {/if}
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Ratepay_phone_div">
                        <label for="register_personal_phone" class="Ratepay_phone_label"><br />{s name="pigmbh_ratepay_phone_text" namespace="Frontend/register/payment_fielset"}Telefon/Handynummer*{/s}</label>
                        <input id="register_personal_phone" class="text pi_ratepay_phone" type="text" value="" name="registerRatePAY[personal][phone_rate]">
                    </div>
                {/if}
                {if $pi_ratepay_company}
                    <div id="company" class="Ratepay_company_div">
                        <label for="register_personal_company" class="Ratepay_company_label"><br />{s name="pigmbh_ratepay_company_text" namespace="Frontend/register/payment_fielset"}Firmenname*{/s}</label>
                        <input id="register_personal_company" class="text pi_ratepay_company" type="text" value="" name="registerRatePAY[personal][company_rate]">
                    </div>
                {elseif $pi_ratepay_ustid}
                    <div id="company" class="Ratepay_ustid_div">
                        <label for="register_personal_ustid" class="Ratepay_ustid_label"><br />{s name="pigmbh_ratepay_ustid_text" namespace="Frontend/register/payment_fielset"}Umsatzsteuer ID*{/s}</label>
                        <input id="register_personal_ustid" class="text pi_ratepay_ustid" type="text" value="" name="registerRatePAY[personal][ustid_rate]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_ustid || $pi_ratepay_company}
                    <input class="pi_ratePAY_savebutton_checkout" name="saveRatepayRateData" type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
                {/if}
            </div>
            {/if}
        </div>
    {elseif $payment_mean.name == "RatePAYDebit"}
        <div class="grid_10 last" id="ratepay_width_checkout">
            {$payment_mean.additionaldescription}
            {if $pi_ratepay_b2b_debit == false}
            <div class="grid_8" id="ratepay_debitbirth" style="float: right; text-align: right;">
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday'])}
                    <div id="birthdate" class="Ratepay_birthday_div">
                    <table style="border: 0px; margin: 0px;  float: right;">
                        <tr>
                            <td>
                                <label for="register_personal_birthdate" class="Ratepay_birthday_label" style="width: 150px;">{s name="pigmbh_ratepay_birthday_text" namespace="Frontend/register/payment_fielset"}Geburtstag(TT/MM/JJJJ)*{/s}</label>
                            </td>
                            <td>
                                <select id="register_personal_birthdate" name="registerRatePAY[personal][birthday_debit]">
                                    <option value="">--</option>
                                    {section name="birthdate" start=1 loop=32 step=1}
                                        <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthmonth_debit]">
                                    <option value="">-</option>
                                    {section name="birthmonth" start=1 loop=13 step=1}
                                        <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                                    {/section}
                                </select>

                                <select name="registerRatePAY[personal][birthyear_debit]">
                                    <option value="">----</option>
                                    {section name="birthyear" loop=2000 max=100 step=-1}
                                        <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                                    {/section}
                                </select>
                           </td>
                        </tr>
                    </table>
                    </div>
                {/if}
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Ratepay_phone_div">
                        <label for="register_personal_phone" class="Ratepay_phone_label"><br />{s name="pigmbh_ratepay_phone_text" namespace="Frontend/register/payment_fielset"}Telefon/Handynummer*{/s}</label>
                        <input id="register_personal_phone" class="text pi_ratepay_phone" type="text" value="" name="registerRatePAY[personal][phone_debit]">
                    </div>
                {/if}
                {if $pi_ratepay_company}
                    <div id="company" class="Ratepay_company_div">
                        <label for="register_personal_company" class="Ratepay_company_label"><br />{s name="pigmbh_ratepay_company_text" namespace="Frontend/register/payment_fielset"}Firmenname*{/s}</label>
                        <input id="register_personal_company" class="text pi_ratepay_company" type="text" value="" name="registerRatePAY[personal][company_debit]">
                    </div>
                {elseif $pi_ratepay_ustid}
                    <div id="company" class="Ratepay_ustid_div">
                        <label for="register_personal_ustid" class="Ratepay_ustid_label"><br />{s name="pigmbh_ratepay_ustid_text" namespace="Frontend/register/payment_fielset"}Umsatzsteuer ID*{/s}</label>
                        <input id="register_personal_ustid" class="text pi_ratepay_ustid" type="text" value="" name="registerRatePAY[personal][ustid_debit]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_ustid || $pi_ratepay_company}
                    <input class="pi_ratePAY_savebutton_checkout" name="saveRatepayDebitData" type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
                {/if}
            </div>
            {/if}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
    {/block}

    {block name='frontend_checkout_payment_fieldset_input_radio'}
    {if ( $pi_ratepay_no_ratepay || $sUserData.billingaddress.birthday=="0000-00-00" || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_company || $pi_ratepay_ustid || $pi_ratepay_toyoung || $pi_ratepay_address || $pi_ratepay_b2b_invoice) && ($payment_mean.name == "RatePAYInvoice")}
        <div id="pi_RatePAY_paymentWarning"><center>
                {if $piRatepayInvoiceWarning == 'b2b'}
                {s name="pigmbh_ratepay_warning_invoice_b2b" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Rechnung nur als Privatkunde m&ouml;glich.{/s}
                {elseif $piRatepayInvoiceWarning == 'all_company'}
                {s name="pigmbh_ratepay_warning_invoice_all_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und den Firmennamen ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'all_ustid'}
                {s name="pigmbh_ratepay_warning_invoice_all_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und die Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'both'}
                {s name="pigmbh_ratepay_warning_invoice_both" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag und Ihre Telefonnummer ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'birthday_company'}
                {s name="pigmbh_ratepay_warning_invoice_birthday_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihren Firmennamen ein{/s}
                {elseif $piRatepayInvoiceWarning == 'birthday_ustid'}
                {s name="pigmbh_ratepay_warning_invoice_birthday_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'birthday'}
                {s name="pigmbh_ratepay_warning_invoice_birthday" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'phone_ustid'}
                {s name="pigmbh_ratepay_warning_invoice_phone_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'phone_company'}
                {s name="pigmbh_ratepay_warning_invoice_phone_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und den Firmenname ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'phone'}
                {s name="pigmbh_ratepay_warning_invoice_phone" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer ein{/s}
                {elseif $piRatepayInvoiceWarning == 'company'}
                {s name="pigmbh_ratepay_warning_invoice_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihren Firmennamen ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'ustid'}
                {s name="pigmbh_ratepay_warning_invoice_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren, geben Sie bitte Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayInvoiceWarning == 'address'}
                {s name="pigmbh_ratepay_warning_invoice_address" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Rechnung durchzuf&uuml;hren m&uuml;ssen Rechnungs- und Lieferaddresse identisch sein{/s}
                {elseif $piRatepayInvoiceWarning == 'toyoung'}
                {s name="pigmbh_ratepay_warning_invoice_toyoung" namespace="frontend/register/payment_fieldset.tpl"}Bitte beachten Sie, dass RatePAY Rechnung erst ab einem Alter von 18 Jahren genutzt werden kann.{/s}
                {elseif $piRatepayInvoiceWarning == 'notaccepted'}
                {s name="pigmbh_ratepay_warning_invoice_notaccepted" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Rechnung nicht m&ouml;glich. Diese Entscheidung ist von RatePAY auf der Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten erfahren Sie in der{/s}
                <label class="RatePAYAgbLabel"><a target="_blank" href="{$datenschutzRatepayInvoice}">{s name="pigmbh_ratepay_warning_invoice_notaccepted_href" namespace="frontend/register/payment_fieldset.tpl"}RatePAY-Datenschutzerkl&auml;rung.{/s}</a></label>
                {/if}</center></div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayInvoiceSurcharge}
                <span style="margin-left: 27px; float: left;">
                    ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayInvoiceSurcharge})
                </span>
            {/if}
        </div>

    {elseif ( $pi_ratepay_no_ratepay || $sUserData.billingaddress.birthday=="0000-00-00" || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_company || $pi_ratepay_ustid || $pi_ratepay_toyoung || $pi_ratepay_address || $pi_ratepay_b2b_rate) && ($payment_mean.name == "RatePAYRate")}
        <div id="pi_RatePAY_paymentWarning"><center>
                {if $piRatepayRateWarning == 'b2b'}
                {s name="pigmbh_ratepay_warning_rate_b2b" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Ratenzahlung nur als Privatkunde m&ouml;glich.{/s}
                {elseif $piRatepayRateWarning == 'all_company'}
                {s name="pigmbh_ratepay_warning_rate_all_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und den Firmennamen ein.{/s}
                {elseif $piRatepayRateWarning == 'all_ustid'}
                {s name="pigmbh_ratepay_warning_rate_all_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und die Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayRateWarning == 'both'}
                {s name="pigmbh_ratepay_warning_rate_both" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag und Ihre Telefonnummer ein.{/s}
                {elseif $piRatepayRateWarning == 'birthday_company'}
                {s name="pigmbh_ratepay_warning_rate_birthday_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihren Firmennamen ein{/s}
                {elseif $piRatepayRateWarning == 'birthday_ustid'}
                {s name="pigmbh_ratepay_warning_rate_birthday_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayRateWarning == 'birthday'}
                {s name="pigmbh_ratepay_warning_rate_birthday" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum ein.{/s}
                {elseif $piRatepayRateWarning == 'phone_ustid'}
                {s name="pigmbh_ratepay_warning_rate_phone_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayRateWarning == 'phone_company'}
                {s name="pigmbh_ratepay_warning_rate_phone_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und den Firmenname ein.{/s}
                {elseif $piRatepayRateWarning == 'phone'}
                {s name="pigmbh_ratepay_warning_rate_phone" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer ein{/s}
                {elseif $piRatepayRateWarning == 'company'}
                {s name="pigmbh_ratepay_warning_rate_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihren Firmennamen ein.{/s}
                {elseif $piRatepayRateWarning == 'ustid'}
                {s name="pigmbh_ratepay_warning_rate_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren, geben Sie bitte Ihre Umasatzsteuer ID ein.{/s}
                {elseif $piRatepayRateWarning == 'address'}
                {s name="pigmbh_ratepay_warning_rate_address" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Ratenzahlung durchzuf&uuml;hren m&uuml;ssen Rechnungs- und Lieferaddresse identisch sein{/s}
                {elseif $piRatepayRateWarning == 'toyoung'}
                {s name="pigmbh_ratepay_warning_rate_toyoung" namespace="frontend/register/payment_fieldset.tpl"}Bitte beachten Sie, dass RatePAY Ratenzahlung erst ab einem Alter von 18 Jahren genutzt werden kann.{/s}
                {elseif $piRatepayRateWarning == 'notaccepted'}
                {s name="pigmbh_ratepay_warning_rate_notaccepted" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Ratenzahlung nicht m&ouml;glich. Diese Entscheidung ist von RatePAY auf der Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten erfahren Sie in der{/s}
                <label class="RatePAYAgbLabel"><a target="_blank" href="{$datenschutzRatepayRate}">{s name="pigmbh_ratepay_warning_rate_notaccepted_href" namespace="frontend/register/payment_fieldset.tpl"}RatePAY-Datenschutzerkl&auml;rung.{/s}</a></label>
                {/if}</center></div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayRateSurcharge}
                <span style="margin-left: 27px; float: left;">
                    ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayRateSurcharge})
                </span>
            {/if}
            {if $activateDebit}
                <span style="margin-left: 27px; float: left;">
                    <select id="paymentFieldsetDebitSelect" name="registerRatePAY[personal][debitPayment]"  disabled="disabled">
                        <option value="bankTransfer">{s name="pigmbh_ratepay_debit_bankTransfer" namespace="frontend/register/payment_fieldset.tpl"}Per &Uuml;berweisung{/s}</option>
                        <option value="directDebit">{s name="pigmbh_ratepay_debit_directDebit" namespace="frontend/register/payment_fieldset.tpl"}Per elektronischem Lastschriftverfahren{/s}</option>
                    </select>
                </span>
            {elseif !$activateDebit}
                <span style="margin-left: 27px; float: left;">
                    <select name="registerRatePAY[personal][debitPayment]" id="paymentFieldsetDebitSelect" style="display:none;">
                        <option value="bankTransfer" {if !$ratepayDebitPayType}selected{/if}>{s name="pigmbh_ratepay_debit_bankTransfer" namespace="frontend/register/payment_fieldset.tpl"}Per &Uuml;berweisung{/s}</option>
                    </select>
                </span>
            {/if}
        </div>
    {elseif ( $pi_ratepay_no_ratepay || $sUserData.billingaddress.birthday=="0000-00-00" || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone'] || $pi_ratepay_company || $pi_ratepay_ustid || $pi_ratepay_toyoung || $pi_ratepay_address || $pi_ratepay_b2b_debit) && ($payment_mean.name == "RatePAYDebit")}
        <div id="pi_RatePAY_paymentWarning"><center>
                {if $piRatepayDebitWarning == 'b2b'}
                {s name="pigmbh_ratepay_warning_debit_b2b" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Lastschrift nur als Privatkunde m&ouml;glich.{/s}
                {elseif $piRatepayDebitWarning == 'all_company'}
                {s name="pigmbh_ratepay_warning_debit_all_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und den Firmennamen ein.{/s}
                {elseif $piRatepayDebitWarning == 'all_ustid'}
                {s name="pigmbh_ratepay_warning_debit_all_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag, Ihre Telefonnummer und die Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayDebitWarning == 'both'}
                {s name="pigmbh_ratepay_warning_debit_both" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihren Geburtstag und Ihre Telefonnummer ein.{/s}
                {elseif $piRatepayDebitWarning == 'birthday_company'}
                {s name="pigmbh_ratepay_warning_debit_birthday_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihren Firmennamen ein{/s}
                {elseif $piRatepayDebitWarning == 'birthday_ustid'}
                {s name="pigmbh_ratepay_warning_debit_birthday_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayDebitWarning == 'birthday'}
                {s name="pigmbh_ratepay_warning_debit_birthday" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihr Geburtsdatum ein.{/s}
                {elseif $piRatepayDebitWarning == 'phone_ustid'}
                {s name="pigmbh_ratepay_warning_debit_phone_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und Ihre Umsatzsteuer ID ein.{/s}
                {elseif $piRatepayDebitWarning == 'phone_company'}
                {s name="pigmbh_ratepay_warning_debit_phone_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer und den Firmenname ein.{/s}
                {elseif $piRatepayDebitWarning == 'phone'}
                {s name="pigmbh_ratepay_warning_debit_phone" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihre Telefonnummer ein{/s}
                {elseif $piRatepayDebitWarning == 'company'}
                {s name="pigmbh_ratepay_warning_debit_company" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihren Firmennamen ein.{/s}
                {elseif $piRatepayDebitWarning == 'ustid'}
                {s name="pigmbh_ratepay_warning_debit_ustid" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, geben Sie bitte Ihre Umasatzsteuer ID ein.{/s}
                {elseif $piRatepayDebitWarning == 'address'}
                {s name="pigmbh_ratepay_warning_debit_address" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren m&uuml;ssen Rechnungs- und Lieferaddresse identisch sein{/s}
                {elseif $piRatepayDebitWarning == 'toyoung'}
                {s name="pigmbh_ratepay_warning_debit_toyoung" namespace="frontend/register/payment_fieldset.tpl"}Bitte beachten Sie, dass RatePAY Lastschrift erst ab einem Alter von 18 Jahren genutzt werden kann.{/s}
                {elseif $piRatepayDebitWarning == 'notaccepted'}
                {s name="pigmbh_ratepay_warning_debit_notaccepted" namespace="frontend/register/payment_fieldset.tpl"}Leider ist eine Bezahlung mit RatePAY Lastschrift nicht m&ouml;glich. Diese Entscheidung ist von RatePAY auf der Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten erfahren Sie in der{/s}
                <label class="RatePAYAgbLabel"><a target="_blank" href="{$datenschutzRatepayDebit}">{s name="pigmbh_ratepay_warning_debit_notaccepted_href" namespace="frontend/register/payment_fieldset.tpl"}RatePAY-Datenschutzerkl&auml;rung.{/s}</a></label>
                {/if}</center></div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayDebitSurcharge}
                <span style="margin-left: 27px; float: left;">
                    ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayDebitSurcharge})
                </span>
            {/if}
        </div>
    {elseif $payment_mean.name == "RatePAYDebit" && (!$debitData.owner || !$debitData.accountnumber || !$debitData.bankcode || !$debitData.bankname)}
        <div id="directDebitWarningTwo"><center>{s name="pigmbh_ratepay_debit_warning" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung per RatePAY Lastschrift durchzuf&uuml;hren, f&uuml;llen Sie bitte alle Felder aus{/s}</center></div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $sPayment.id} checked="checked"{/if}/> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayDebitSurcharge}
                <span style="margin-left: 27px; float: left;">
                    ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayDebitSurcharge})
                </span>
            {/if}
        </div>
    {elseif $payment_mean.name == "RatePAYDebit" || $payment_mean.name == "RatePAYRate" || $payment_mean.name == "RatePAYInvoice"}
        {if $payment_mean.name == "RatePAYRate" && $activateDebit && (!$debitData.owner || !$debitData.accountnumber || !$debitData.bankcode || !$debitData.bankname)}
            <div id="directDebitWarningTwo"><center>{s name="pigmbh_ratepay_debit_debitwarning" namespace="frontend/register/payment_fieldset.tpl"}Um eine Zahlung mit RatePAY Rate per elektronischer Lastschrift durchzuf&uuml;hren, f&uuml;llen Sie bitte alle Felder aus{/s}</center></div>
        {/if}
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $sUserData.additional.payment.id} checked="checked"{/if} />
            <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $ratepayDebitSurcharge || $ratepayRateSurcharge || $ratepayInvoiceSurcharge}
                <span style="margin-left: 27px; float: left;">
                    {if $payment_mean.name == "RatePAYDebit" && $ratepayDebitSurcharge}
                        ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayDebitSurcharge})
                    {elseif $payment_mean.name == "RatePAYRate" && $ratepayRateSurcharge}
                        ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayRateSurcharge})
                    {elseif $payment_mean.name == "RatePAYInvoice" && $ratepayInvoiceSurcharge}
                        ({s name="pigmbh_ratepay_paymentfees" namespace="frontend/register/payment_fieldset.tpl"}Aufschlag{/s}:&nbsp;{$ratepayInvoiceSurcharge})
                    {/if}
                </span>
            {/if}
            {if $payment_mean.name == "RatePAYRate" && $activateDebit}
                <span style="margin-left: 27px; float: left;">
                    <select name="registerRatePAY[personal][debitPayment]" id="paymentFieldsetDebitSelect">
                        <option value="bankTransfer" {if !$ratepayDebitPayType}selected{/if}>{s name="pigmbh_ratepay_debit_bankTransfer" namespace="frontend/register/payment_fieldset.tpl"}Per &Uuml;berweisung{/s}</option>
                        <option value="directDebit"{if $ratepayDebitPayType}selected{/if}>{s name="pigmbh_ratepay_debit_directDebit" namespace="frontend/register/payment_fieldset.tpl"}Per elektronischem Lastschriftverfahren{/s}</option>
                    </select>
                </span>
            {elseif $payment_mean.name == "RatePAYRate" && !$activateDebit}
                <span style="margin-left: 27px; float: left;">
                    <select name="registerRatePAY[personal][debitPayment]" id="paymentFieldsetDebitSelect" style="display:none;">
                        <option value="bankTransfer" {if !$ratepayDebitPayType}selected{/if}>{s name="pigmbh_ratepay_debit_bankTransfer" namespace="frontend/register/payment_fieldset.tpl"}Per &Uuml;berweisung{/s}</option>
                    </select>
                </span>
            {/if}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
    {/block}

    {block name='frontend_checkout_error_messages'}
    {if $sUserData['billingaddress']['birthday']=='0000-00-00' || empty($sUserData['billingaddress']['birthday']) || !$sUserData['billingaddress']['phone']}
        {if $pi_ratepay_PaymentError!=false}
            <div class="error agb_confirm" style="margin:0">
                <div class="center">
                    <strong>
                        {$pi_ratepay_PaymentError}
                    </strong>
                </div>
            </div>
        {else}
            {$smarty.block.parent}
        {/if}
    {/if}
    {/block}
{/if}

{block name='frontend_register_payment_fieldset_template' append}
{if  $payment_mean.name == "RatePAYDebit"&&  !$pi_ratepay_no_ratepay}
    {if $sUserData['billingaddress']['birthday']!='0000-00-00' && $sUserData['billingaddress']['phone'] && !$pi_ratepay_company && !$pi_ratepay_ustid}
        <div class="Debit" id="ratepayAcountDebit">
            <p class="none">
                <label for="owner" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_owner" namespace="frontend/register/payment_fieldset.tpl"}Kontoinhaber*{/s}</label>
                <input name="ratepayDebit[owner]"  type="text" id="owner" value="{$debitData.owner}" class="text {if $debitData.owner}instyle_success {elseif !$debitData.owner && $RatepayDebitMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="accountnumber" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_accountnumber" namespace="frontend/register/payment_fieldset.tpl"}Kontonummer*{/s}</label>
                <input name="ratepayDebit[accountnumber]" type="text" id="accountnumber" value="{$debitData.accountnumber}" class="text {if $debitData.accountnumber}instyle_success {elseif !$debitData.accountnumber && $RatepayDebitMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="bankcode" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_bankcode" namespace="frontend/register/payment_fieldset.tpl"}Bankleitzahl*{/s}</label>
                <input name="ratepayDebit[bankcode]" type="text" id="bankcode" value="{$debitData.bankcode}" class="text {if $debitData.bankcode}instyle_success {elseif !$debitData.bankcode && $RatepayDebitMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="bankname" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_bankname" namespace="frontend/register/payment_fieldset.tpl"}Name der Bank*{/s}</label>
                <input name="ratepayDebit[bankname]"type="text" id="bank" value="{$debitData.bankname}" class="text {if $debitData.bankname}instyle_success {elseif !$debitData.bankname && $RatepayDebitMissingBankData}instyle_error{/if}" />
            </p>
            <input type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
        </div>
    {/if}
{/if}
{/block}

{block name='frontend_checkout_payment_fieldset_template' append}
{if  $payment_mean.name == "RatePAYDebit" &&  !$pi_ratepay_no_ratepay}
    {if $sUserData['billingaddress']['birthday']!='0000-00-00' && $sUserData['billingaddress']['phone'] && !$pi_ratepay_company && !$pi_ratepay_ustid}
        <div class="Debit" id="ratepayDebitCheckout">
            <p class="none">
                <label for="owner" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_owner" namespace="frontend/register/payment_fieldset.tpl"}Kontoinhaber*{/s}</label>
                <input name="ratepayDebit[owner]"  type="text" id="owner" value="{$debitData.owner}" class="text {if $debitData.owner}instyle_success {elseif !$debitData.owner && $RatepayDebitMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="accountnumber" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_accountnumber" namespace="frontend/register/payment_fieldset.tpl"}Kontonummer*{/s}</label>
                <input name="ratepayDebit[accountnumber]" type="text" id="accountnumber" value="{$debitData.accountnumber}" class="text {if $debitData.accountnumber}instyle_success {elseif !$debitData.accountnumber && $RatepayDebitMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="bankcode" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_bankcode" namespace="frontend/register/payment_fieldset.tpl"}Bankleitzahl*{/s}</label>
                <input name="ratepayDebit[bankcode]" type="text" id="bankcode" value="{$debitData.bankcode}" class="text {if $debitData.bankcode}instyle_success {elseif !$debitData.bankcode && $RatepayDebitMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="bankname" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_bankname" namespace="frontend/register/payment_fieldset.tpl"}Name der Bank*{/s}</label>
                <input name="ratepayDebit[bankname]"type="text" id="bank" value="{$debitData.bankname}" class="text {if $debitData.bankname}instyle_success {elseif !$debitData.bankname && $RatepayDebitMissingBankData}instyle_error{/if}" />
            </p>
            <input type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
        </div>
    {/if}
{/if}
{/block}

{block name='frontend_register_payment_fieldset_template' append}
{if  $payment_mean.name == "RatePAYRate"&&  !$pi_ratepay_no_ratepay}
    {if $sUserData['billingaddress']['birthday']!='0000-00-00' && $sUserData['billingaddress']['phone'] && !$pi_ratepay_company && !$pi_ratepay_ustid}
        <div class="Debit" id="ratepayDirectDebit" style="margin: 0 0 0 255px;">
            <p class="none">
                <label for="owner" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_owner" namespace="frontend/register/payment_fieldset.tpl"}Kontoinhaber*{/s}</label>
                <input name="ratepayRateDebit[owner]"  type="text" id="owner" value="{$debitData.owner}" class="text {if $debitData.owner}instyle_success {elseif !$debitData.owner && $RatepayRateMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="accountnumber" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_accountnumber" namespace="frontend/register/payment_fieldset.tpl"}Kontonummer*{/s}</label>
                <input name="ratepayRateDebit[accountnumber]" type="text" id="accountnumber" value="{$debitData.accountnumber}" class="text {if $debitData.accountnumber}instyle_success {elseif !$debitData.accountnumber && $RatepayRateMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="bankcode" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_bankcode" namespace="frontend/register/payment_fieldset.tpl"}Bankleitzahl*{/s}</label>
                <input name="ratepayRateDebit[bankcode]" type="text" id="bankcode" value="{$debitData.bankcode}" class="text {if $debitData.bankcode}instyle_success {elseif !$debitData.bankcode && $RatepayRateMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="bankname" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_bankname" namespace="frontend/register/payment_fieldset.tpl"}Name der Bank*{/s}</label>
                <input name="ratepayRateDebit[bankname]"type="text" id="bank" value="{$debitData.bankname}" class="text {if $debitData.bankname}instyle_success {elseif !$debitData.bankname && $RatepayRateMissingBankData}instyle_error{/if}" />
            </p>
            <input type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
        </div>
    {/if}
{/if}
{/block}

{block name='frontend_checkout_payment_fieldset_template' append}
{if  $payment_mean.name == "RatePAYRate"&&  !$pi_ratepay_no_ratepay}
    {if $sUserData['billingaddress']['birthday']!='0000-00-00' && $sUserData['billingaddress']['phone'] && !$pi_ratepay_company && !$pi_ratepay_ustid}
        <div class="Debit" id="ratepayDirectDebit" style="margin: 0 0 0 255px;">
            <p class="none">
                <label for="owner" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_owner" namespace="frontend/register/payment_fieldset.tpl"}Kontoinhaber*{/s}</label>
                <input name="ratepayRateDebit[owner]"  type="text" id="owner" value="{$debitData.owner}" class="text {if $debitData.owner}instyle_success {elseif !$debitData.owner && $RatepayRateMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="accountnumber" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_accountnumber" namespace="frontend/register/payment_fieldset.tpl"}Kontonummer*{/s}</label>
                <input name="ratepayRateDebit[accountnumber]" type="text" id="accountnumber" value="{$debitData.accountnumber}" class="text {if $debitData.accountnumber}instyle_success {elseif !$debitData.accountnumber && $RatepayRateMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="bankcode" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_bankcode" namespace="frontend/register/payment_fieldset.tpl"}Bankleitzahl*{/s}</label>
                <input name="ratepayRateDebit[bankcode]" type="text" id="bankcode" value="{$debitData.bankcode}" class="text {if $debitData.bankcode}instyle_success {elseif !$debitData.bankcode && $RatepayRateMissingBankData}instyle_error{/if}" />
            </p>
            <p class="none">
                <label for="bankname" id="ratepayDebitLabel">{s name="pigmbh_ratepay_debit_bankname" namespace="frontend/register/payment_fieldset.tpl"}Name der Bank*{/s}</label>
                <input name="ratepayRateDebit[bankname]"type="text" id="bank" value="{$debitData.bankname}" class="text {if $debitData.bankname}instyle_success {elseif !$debitData.bankname && $RatepayRateMissingBankData}instyle_error{/if}" />
            </p>
            <input type="submit" value='{s name="pigmbh_ratepay_submit_value" namespace="Frontend/register/payment_fielset"}speichern{/s}' />
        </div>
    {/if}
{/if}
{/block}