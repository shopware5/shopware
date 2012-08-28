{if $pi_klarna_viewport == "account" || pi_klarna_viewport == "register"}
    {block name="frontend_register_payment_fieldset_description"}
        {if $payment_mean.name == "KlarnaInvoice" || $payment_mean.name == "KlarnaPartPayment"}
            {if $payment_mean.name == "KlarnaInvoice"}
                    <div class="grid_10 last" id="KlarnaInvoiceDiv">
                    {if $klarnaWrongCurrency}
                            <script type="text/javascript" >
                                    document.getElementById("KlarnaInvoiceDiv").parentNode.style.display="none";
                            </script>
                    {/if}
                {$pi_Klarna_lang['invoice']['description']}
                (<a href="#" title="{$pi_Klarna_lang['invoice']['href']}" id="klarna_invoice" class="Klarnacolor" onclick="ShowKlarnaInvoicePopup();return false;"></a>)
                <br/><br/>
                <a href="https://klarna.com/de/privatpersonen/unsere-services/klarna-rechnung" title="{$pi_Klarna_lang['invoice']['href']}" target="_blank">
                    <img src="{$piKlarnaImgDir|cat:'KlarnaInvoiceLogo.png'}" />
                </a>
            {elseif $payment_mean.name == "KlarnaPartPayment"}
                    <div class="grid_10 last" id="KlarnaRatepayDiv">
                    {if !$RateIsTrue || $klarnaWrongCurrency}
                            <script type="text/javascript" >
                                    document.getElementById("KlarnaRatepayDiv").parentNode.style.display="none";
                            </script>
                    {/if}
                {$pi_Klarna_lang['rate']['description']}
                (<a href="#" class="Klarnacolor" title="{$pi_Klarna_lang['rate']['href']}" id="klarna_partpayment" onclick="ShowKlarnaPartPaymentPopup();return false;"></a>)
                <br /><br/>
                <a href="https://klarna.com/de/privatpersonen/unsere-services/klarna-ratenkauf" title="{$pi_Klarna_lang['rate']['href']}" target="_blank">
                    <img src="{$piKlarnaImgDir|cat:'KlarnaRatepayLogo.png'}"  class="KlarnaFloatLeft"/>
                </a>
                {if $sUserData.additional.country.countryiso == "NL"}
                    <div class="KlarnaNetherlandWarningDiv">
                            <img src="{$piKlarnaImgDir|cat:'klarnaNetherlandsWarning.jpg'}" class="KlarnaNetherlandWarningImg KlarnaFloatLeft" />
                    </div>
                {/if}
            {/if}
            </div>
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}

    {block name='frontend_register_payment_fieldset_input_radio'}
            {if $payment_mean.name == "KlarnaPartPayment"}
                    {if ($PigmbhKlarnaPaymentRateWarningText || $klarnaDenied) && ($sPaymentErrorMethod == $payment_mean.name || $sPaymentRegisterError) || !$RateIsTrue}
                            <div id="PigmbhKlarnaPaymentWarningAccount"><center>{$PigmbhKlarnaPaymentRateWarningText}</center></div>
                            <div class="grid_5 first">
                            {if (!$piKlarnaSesssionWarning || $klarnaDenied) && ($sPaymentErrorMethod == $payment_mean.name || $sPaymentRegisterError) || !$RateIsTrue}
                                <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" />
                                <label for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                            {else}
                                <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $sUserData.additional.payment.id or $payment_mean.id eq $form_data.payment} checked="checked"{/if} />
                                <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                            {/if}
                    {else}
                            <div class="grid_5 first">
                            <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $sUserData.additional.payment.id or $payment_mean.id eq $form_data.payment} checked="checked"{/if} />
                            <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                    {/if}
                    {if $RateIsTrue}
                            <br/>
                            <span class="Klarna_radio_span">{$pi_Klarna_lang['rate']['from']} {$pi_klarna_rateAmount} {$piKlarnaShopCurrency}{$pi_Klarna_lang['rate']['value_month']|replace:$pi_Klarna_lang['currency']:""}</span>
                            {if $NorwayTotalCost}
                                <br/>
                                <div class="KlarnaNorwayRateDiv">({$pi_Klarna_lang['Norway']['total']} {$NorwayTotalCost} {$pi_Klarna_lang['Norway']['ratetext']} {$NorwayAprCost}%)</div>
                            {/if}
                    {/if}
                </div>
            {elseif $payment_mean.name == "KlarnaInvoice"}
                    {if ($PigmbhKlarnaPaymentRateWarningText || $klarnaDenied) && ($sPaymentErrorMethod == $payment_mean.name || $sPaymentRegisterError)}
                            <div id="PigmbhKlarnaPaymentWarningAccount"><center>{$PigmbhKlarnaPaymentInvoiceWarningText}</center></div>
                            <div class="grid_5 first">
                            {if (!$piKlarnaSesssionWarning || $klarnaDenied) && ($sPaymentErrorMethod == $payment_mean.name || $sPaymentRegisterError)}
                                <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" />
                                <label for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                            {else}
                                <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $sUserData.additional.payment.id or $payment_mean.id eq $form_data.payment} checked="checked"{/if} />
                                <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                            {/if}
                    {else}
                            <div class="grid_5 first">
                            <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $sPayment.id or $payment_mean.id eq $form_data.payment} checked="checked"{/if} />
                            <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                    {/if}
                </div>
            {else}
                {$smarty.block.parent}
            {/if}
    {/block}

    {block name='frontend_register_payment_fieldset_template' prepend}
        {$myBirthday ="-"|explode:$sUserData.billingaddress.birthday}
        {if $payment_mean.name == "KlarnaInvoice" && !$sAddressError &&($sUserData.billingaddress.birthday == '0000-00-00' && (($piKlarnaCountryIso != "DK" && $piKlarnaCountryIso != "NO" && $piKlarnaCountryIso != "FI" && $piKlarnaCountryIso != "SE"))
            || (!$sUserData.billingaddress.text4 &&($piKlarnaCountryIso == "DK" || $piKlarnaCountryIso == "NO" || $piKlarnaCountryIso == "FI" || $piKlarnaCountryIso == "SE")))}
            <fieldset class="KlarnaInvoiceFieldset">
                <legend class="KlarnaInvoiceLegend">{$pi_Klarna_lang['missingInfo']}</legend>
                <div class="KlarnaInvoiceBirthdayDiv">
                        {if !$sUserData.billingaddress.text4 && ($piKlarnaCountryIso == "DK" || $piKlarnaCountryIso == "NO" || $piKlarnaCountryIso == "FI" || $piKlarnaCountryIso == "SE")}
                        <div class="KlarnaHouseExtDiv">
                <label class="KlarnaSocialNrLabel">{$pi_Klarna_lang['SocialNr']}</label>
                <input class="text" type="text" value="" name="klarnaRegister[personal][additional]">
            </div>
            {elseif $sUserData.billingaddress.birthday == '0000-00-00' && ($piKlarnaCountryIso != "DK" && $piKlarnaCountryIso != "NO" && $piKlarnaCountryIso != "FI" && $piKlarnaCountryIso != "SE")}
                <label class="KlarnaLabelAccount">{$pi_Klarna_lang['birthday']}</label>
                <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthday]">
                    <option value="">--</option>
                    {section name="birthdate" start=1 loop=32 step=1}
                        <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                    {/section}
                </select>
                <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthmonth]">
                    <option value="">--</option>
                    {section name="birthmonth" start=1 loop=13 step=1}
                        <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                    {/section}
                </select>
                <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthyear]">
                    <option value="">----</option>
                    {section name="birthyear" loop=2000 max=100 step=-1}
                        <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                    {/section}
                </select>
           {/if}
            </div>
            <div class="KlarnaSubmitDiv">
               <input class="KlarnaSubmit" name="KlarnaSubmit" type="submit" value='{$pi_Klarna_lang['submit_value']}' />
            </div>
            </fieldset>
        {elseif $payment_mean.name == "KlarnaPartPayment" && $RateIsTrue && !$sAddressError &&($sUserData.billingaddress.birthday == '0000-00-00' &&(($piKlarnaCountryIso != "DK" && $piKlarnaCountryIso != "NO" && $piKlarnaCountryIso != "FI" && $piKlarnaCountryIso != "SE"))
            || (!$sUserData.billingaddress.text4 &&($piKlarnaCountryIso == "DK" || $piKlarnaCountryIso == "NO" || $piKlarnaCountryIso == "FI" || $piKlarnaCountryIso == "SE")))}
            <fieldset class="KlarnaRatepayFieldset" id="KlarnaRatepayFieldset">
                <legend class="KlarnaRatepayLegend">{$pi_Klarna_lang['missingInfo']}</legend>
                <div class="KlarnaRatepayBirthdayDiv">
                        {if !$sUserData.billingaddress.text4 && ($piKlarnaCountryIso == "DK" || $piKlarnaCountryIso == "NO" || $piKlarnaCountryIso == "FI" || $piKlarnaCountryIso == "SE")}
                        <div class="KlarnaHouseExtDiv">
                <label class="KlarnaSocialNrLabel">{$pi_Klarna_lang['SocialNr']}</label>
                <input class="text" type="text" value="" name="klarnaRegister[personal][additionalRate]">
            </div>
                        {elseif $sUserData.billingaddress.birthday == '0000-00-00' && ($piKlarnaCountryIso != "DK" && $piKlarnaCountryIso != "NO" && $piKlarnaCountryIso != "FI" && $piKlarnaCountryIso != "SE")}
                    <label class="KlarnaLabelAccount">{$pi_Klarna_lang['birthday']}</label>
                    <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthdayRate]">
                        <option value="">--</option>
                        {section name="birthdate_invoice" start=1 loop=32 step=1}
                            <option value="{$smarty.section.birthdate_invoice.index}" {if $smarty.section.birthdate_invoice.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate_invoice.index}</option>
                        {/section}
                    </select>
                    <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthmonthRate]">
                        <option value="">--</option>
                        {section name="birthmonth_invoice" start=1 loop=13 step=1}
                            <option value="{$smarty.section.birthmonth_invoice.index}" {if $smarty.section.birthmonth_invoice.index eq $form_data.birthmonth_invoice}selected{/if}>{$smarty.section.birthmonth_invoice.index}</option>
                        {/section}
                    </select>
                    <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthyearRate]">
                        <option value="">----</option>
                        {section name="birthyear_invoice" loop=2000 max=100 step=-1}
                            <option value="{$smarty.section.birthyear_invoice.index}" {if $smarty.section.birthyear_invoice.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear_invoice.index}</option>
                        {/section}
                    </select>
                   {/if}
                </div>
                <div class="KlarnaSubmitDiv">
                       <input class="KlarnaSubmit" name="KlarnaSubmit" type="submit" value='{$pi_Klarna_lang['submit_value']}' />
                    </div>
            </fieldset>
        {/if}
    {/block}


{elseif $pi_klarna_viewport == "checkout"}
	{block name="frontend_checkout_payment_fieldset_description"}
	{if $payment_mean.name == "KlarnaInvoice" || $payment_mean.name == "KlarnaPartPayment"}
            {if $payment_mean.name == "KlarnaInvoice"}
                    <div class="grid_10 last" id="KlarnaInvoiceDiv">
                    {if $klarnaWrongCurrency}
                            <script type="text/javascript" >
                                    document.getElementById("KlarnaInvoiceDiv").parentNode.style.display="none";
                            </script>
                    {/if}
                {$pi_Klarna_lang['invoice']['description']}
                (<a href="#" title="{$pi_Klarna_lang['invoice']['href']}" id="klarna_invoice" class="Klarnacolor" onclick="ShowKlarnaInvoicePopup();return false;"></a>)
                <br/><br/>
                <a href="https://klarna.com/de/privatpersonen/unsere-services/klarna-rechnung" title="{$pi_Klarna_lang['invoice']['href']}" target="_blank">
                    <img src="{$piKlarnaImgDir|cat:'KlarnaInvoiceLogo.png'}" />
                </a>
            {elseif $payment_mean.name == "KlarnaPartPayment"}
                <div class="grid_10 last" id="KlarnaRatepayDiv">
                {if !$RateIsTrue || $klarnaWrongCurrency}
                    <script type="text/javascript" >
                        document.getElementById("KlarnaRatepayDiv").parentNode.style.display="none";
                    </script>
                {/if}
                {$pi_Klarna_lang['rate']['description']}
                (<a href="#" class="Klarnacolor" title="{$pi_Klarna_lang['rate']['href']}" id="klarna_partpayment" onclick="ShowKlarnaPartPaymentPopup();return false;"></a>)
                <br/><br/>
                <a href="https://klarna.com/de/privatpersonen/unsere-services/klarna-ratenkauf" title="{$pi_Klarna_lang['rate']['href']}" target="_blank">
                    <img src="{$piKlarnaImgDir|cat:'KlarnaRatepayLogo.png'}" class="KlarnaFloatLeft"/><br />
                </a>
                {if $sUserData.additional.country.countryiso == "NL"}
                    <div class="KlarnaNetherlandWarningDiv">
                        <img src="{$piKlarnaImgDir|cat:'klarnaNetherlandsWarning.jpg'}" class="KlarnaNetherlandWarningImg KlarnaFloatLeft" />
                    </div>
                {/if}
            {/if}
	    </div>
	{else}
	    {$smarty.block.parent}
	{/if}
	{/block}
	{block name='frontend_checkout_payment_fieldset_template' prepend}
		{$myBirthday ="-"|explode:$sUserData.billingaddress.birthday}
        {if $payment_mean.name == "KlarnaInvoice" && !$sAddressError &&(($sUserData.billingaddress.birthday == '0000-00-00' &&($piKlarnaCountryIso != "DK" && $piKlarnaCountryIso != "NO" && $piKlarnaCountryIso != "FI" && $piKlarnaCountryIso != "SE"))
		    || (!$sUserData.billingaddress.text4 &&($piKlarnaCountryIso == "DK" || $piKlarnaCountryIso == "NO" || $piKlarnaCountryIso == "FI" || $piKlarnaCountryIso == "SE")))}
            <fieldset class="KlarnaInvoiceFieldset">
                <legend class="KlarnaInvoiceLegend">{$pi_Klarna_lang['missingInfo']}</legend>
                <div class="KlarnaInvoiceBirthdayDiv">
                    {if !$sUserData.billingaddress.text4 && ($piKlarnaCountryIso == "DK" || $piKlarnaCountryIso == "NO" || $piKlarnaCountryIso == "FI" || $piKlarnaCountryIso == "SE")}
                        <div class="KlarnaHouseExtDiv">
                            <label class="KlarnaSocialNrLabel">{$pi_Klarna_lang['SocialNr']}</label>
                            <input class="text" type="text" value="" name="klarnaRegister[personal][additional]">
                        </div>
                    {elseif $sUserData.billingaddress.birthday == '0000-00-00' && ($piKlarnaCountryIso != "DK" && $piKlarnaCountryIso != "NO" && $piKlarnaCountryIso != "FI" && $piKlarnaCountryIso != "SE")}
                        <label class="KlarnaLabelAccount">{$pi_Klarna_lang['birthday']}</label>
                        <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthday]">
                            <option value="">--</option>
                            {section name="birthdate" start=1 loop=32 step=1}
                                <option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                            {/section}
                        </select>
                        <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthmonth]">
                            <option value="">--</option>
                            {section name="birthmonth" start=1 loop=13 step=1}
                                <option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                            {/section}
                        </select>
                        <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthyear]">
                            <option value="">----</option>
                            {section name="birthyear" loop=2000 max=100 step=-1}
                                <option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                            {/section}
                        </select>
                    {/if}
                </div>
                <div class="KlarnaSubmitDiv">
                       <input class="KlarnaSubmit" name="KlarnaSubmit" type="submit" value='{$pi_Klarna_lang['submit_value']}' />
                    </div>
            </fieldset>
        {elseif $payment_mean.name == "KlarnaPartPayment" && $RateIsTrue && !$sAddressError &&(($sUserData.billingaddress.birthday == '0000-00-00' &&($piKlarnaCountryIso != "DK" && $piKlarnaCountryIso != "NO" && $piKlarnaCountryIso != "FI" && $piKlarnaCountryIso != "SE"))
            || (!$sUserData.billingaddress.text4 &&($piKlarnaCountryIso == "DK" || $piKlarnaCountryIso == "NO" || $piKlarnaCountryIso == "FI" || $piKlarnaCountryIso == "SE")))}
            <fieldset class="KlarnaRatepayFieldset" id="KlarnaRatepayFieldset">
                <legend class="KlarnaRatepayLegend">{$pi_Klarna_lang['missingInfo']}</legend>
                <div class="KlarnaRatepayBirthdayDiv">
                    {if !$sUserData.billingaddress.text4 && ($piKlarnaCountryIso == "DK" || $piKlarnaCountryIso == "NO" || $piKlarnaCountryIso == "FI" || $piKlarnaCountryIso == "SE")}
                        <div class="KlarnaHouseExtDiv">
                <label class="KlarnaSocialNrLabel">{$pi_Klarna_lang['SocialNr']}</label>
                <input class="text" type="text" value="" name="klarnaRegister[personal][additionalRate]">
            </div>
            {elseif $sUserData.billingaddress.birthday == '0000-00-00' && ($piKlarnaCountryIso != "DK" && $piKlarnaCountryIso != "NO" && $piKlarnaCountryIso != "FI" && $piKlarnaCountryIso != "SE")}
                <label class = "KlarnaLabelAccount">{$pi_Klarna_lang['birthday']}</label>
                <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthdayRate]">
                    <option value="">--</option>
                    {section name="birthdate_invoice" start=1 loop=32 step=1}
                        <option value="{$smarty.section.birthdate_invoice.index}" {if $smarty.section.birthdate_invoice.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate_invoice.index}</option>
                    {/section}
                </select>

                <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthmonthRate]">
                    <option value="">--</option>
                    {section name="birthmonth_invoice" start=1 loop=13 step=1}
                        <option value="{$smarty.section.birthmonth_invoice.index}" {if $smarty.section.birthmonth_invoice.index eq $form_data.birthmonth_invoice}selected{/if}>{$smarty.section.birthmonth_invoice.index}</option>
                    {/section}
                </select>
                <select class ="KlarnaBirthdaySelect" name="klarnaRegister[personal][birthyearRate]">
                    <option value="">----</option>
                    {section name="birthyear_invoice" loop=2000 max=100 step=-1}
                        <option value="{$smarty.section.birthyear_invoice.index}" {if $smarty.section.birthyear_invoice.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear_invoice.index}</option>
                    {/section}
                </select>
            {/if}
            </div>
            {if $piKlarnaCountryIso == "NL"}
                    <script type="text/javascript" >
                            document.getElementById("KlarnaRatepayFieldset").style.margin="0 11px -90px 0";
                            document.getElementById("KlarnaRatepayFieldset").style.bottom="80px";
                    </script>
            {/if}
            <div class="KlarnaSubmitDiv">
                   <input class="KlarnaSubmit" name="KlarnaSubmit" type="submit" value='{$pi_Klarna_lang['submit_value']}' />
                </div>
            </fieldset>
        {/if}
    {/block}

    {block name='frontend_checkout_payment_fieldset_input_radio'}
        {if $payment_mean.name == "KlarnaPartPayment"}
            {if ($PigmbhKlarnaPaymentRateWarningText || $klarnaDenied) && ($sPaymentErrorMethod == $payment_mean.name || $sPaymentRegisterError) || !$RateIsTrue}
                <div id="PigmbhKlarnaPaymentWarning"><center>{$PigmbhKlarnaPaymentRateWarningText}</center></div>
                <div class="grid_5 first">
                {if (!$piKlarnaSesssionWarning || $klarnaDenied) && ($sPaymentErrorMethod == $payment_mean.name || $sPaymentRegisterError) || !$RateIsTrue}
                    <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" />
                    <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                {else}
                    <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $sUserData.additional.payment.id or $payment_mean.id eq $sPayment.id} checked="checked"{/if} />
                    <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                {/if}
            {else}
                <div class="grid_5 first">
                <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $sUserData.additional.payment.id or $payment_mean.id eq $sPayment.id} checked="checked"{/if} />
                <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {/if}
            {if $RateIsTrue}
                <br/>
                <span class="Klarna_radio_span">{$pi_Klarna_lang['rate']['from']} {$pi_klarna_rateAmount} {$piKlarnaShopCurrency}{$pi_Klarna_lang['rate']['value_month']|replace:$pi_Klarna_lang['currency']:""}</span>
                {if $NorwayTotalCost}
                    <br/>
                    <div class="KlarnaNorwayRateDiv">({$pi_Klarna_lang['Norway']['total']} {$NorwayTotalCost} {$pi_Klarna_lang['Norway']['ratetext']} {$NorwayAprCost}%)</div>
                {/if}
            {/if}
            </div>
        {elseif $payment_mean.name == "KlarnaInvoice"}
            {if ($PigmbhKlarnaPaymentRateWarningText || $klarnaDenied) && ($sPaymentErrorMethod == $payment_mean.name || $sPaymentRegisterError)}
                <div id="PigmbhKlarnaPaymentWarning"><center>{$PigmbhKlarnaPaymentInvoiceWarningText}</center></div>
                <div class="grid_5 first">
                {if (!$piKlarnaSesssionWarning || $klarnaDenied) && ($sPaymentErrorMethod == $payment_mean.name || $sPaymentRegisterError)}
                    <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" disabled="disabled" />
                        <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                {else}
                    <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" {if $payment_mean.id eq $sPayment.id} checked="checked"{/if} />
                    <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                {/if}
            {else}
                <div class="grid_5 first">
                <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $sPayment.id} checked="checked"{/if} />
                <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {/if}
            </div>
        {else}
            {$smarty.block.parent} 
        {/if}
    {/block}
{/if}
