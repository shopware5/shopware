{if $pi_Paymorrow_Viewport == "account" || $pi_Paymorrow_Viewport == "register"}
    {block name='frontend_register_payment_fieldset_description'}
        {if $payment_mean.name == "PaymorrowInvoice"}
        <div class="grid_10 last" id="Paymorrow_width_register">
            <img src="{link file='engine/Shopware/Plugins/Default/Frontend/PiPaymorrowPayment/img/checkout_Logo_ol_rechnung_small_trans.png' fullPath}"/>
            <br/><br/>
            {$payment_mean.additionaldescription}
            <div class="grid_8 bankdata"  id="Paymorrow_invoicebirth"  style="left: 0px;">
                {if $sUserData['billingaddress']['birthday']=='0000-00-00'}
                    <div id="birthdate" class="Paymorrow_birthday_div" style="left: 0px;">
                        <label for="register_personal_birthdate"
                               id="Paymorrow_birthday_label" style="width: 170px;>{$pi_Paymorrow_lang['birthday']['text']}</label>
                        <select id="register_personal_birthdate" name="register[personal][birthday]">
                            <option value="">--</option>
                            {section name="birthdate" start=1 loop=32 step=1}
                                <option value="{$smarty.section.birthdate.index}"
                                        {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                            {/section}
                        </select>

                        <select name="register[personal][birthmonth]">
                            <option value="">-</option>
                            {section name="birthmonth" start=1 loop=13 step=1}
                                <option value="{$smarty.section.birthmonth.index}"
                                        {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                            {/section}
                        </select>

                        <select name="register[personal][birthyear]">
                            <option value="">----</option>
                            {section name="birthyear" loop=2000 max=100 step=-1}
                                <option value="{$smarty.section.birthyear.index}"
                                        {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                            {/section}
                        </select>
                    </div>
                {/if}
                <input type="hidden" value="{$payment_mean.name}" name="payment_name">
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Paymorrow_phone_div">
                        <label for="register_personal_phone"
                               id="Paymorrow_phone_label"><br/>{$pi_Paymorrow_lang['phone']['text']}</label>
                        <input id="register_personal_phone" class="text pi_Paymorrow_phone" type="text" value=""
                               name="register[personal][phone]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || !$sUserData['billingaddress']['phone'] || $pi_Paymorrow_ustid || $pi_Paymorrow_company}
                    <input class="pi_Paymorrow_savebutton" name="pi_Paymorrow_saveBirthday" type="submit"
                           value='{$pi_Paymorrow_lang['submit_value']}'/>
                {/if}
            </div>
        </div>
            {elseif $payment_mean.name == "PaymorrowRate"}
        <div class="grid_10 last" style="left: 0px;" id="Paymorrow_width_register">
            <img src="{link file='engine/Shopware/Plugins/Default/Frontend/PiPaymorrowPayment/img/checkout_Logo_ol_raten_small_trans.png' fullPath}"/>
            <br/><br/>
            {$payment_mean.additionaldescription}
            <div class="grid_8 bankdata" id="Paymorrow_invoicebirth"  style="left: 0px;">
                {if $sUserData['billingaddress']['birthday'] == '0000-00-00'}
                    <div id="birthdate" class="Paymorrow_birthday_div" style="left: 0px;">
                        <label for="register_personal_birthdate"
                               id="Paymorrow_birthday_label" style="width: 170px;>{$pi_Paymorrow_lang['birthday']['text']}</label>
                        <select id="register_personal_birthdate" name="register[personal][birthday_rate]">
                            <option value="">--</option>
                            {section name="birthdate" start=1 loop=32 step=1}
                                <option value="{$smarty.section.birthdate.index}"
                                        {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                            {/section}
                        </select>

                        <select name="register[personal][birthmonth_rate]">
                            <option value="">-</option>
                            {section name="birthmonth" start=1 loop=13 step=1}
                                <option value="{$smarty.section.birthmonth.index}"
                                        {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                            {/section}
                        </select>

                        <select name="register[personal][birthyear_rate]">
                            <option value="">----</option>
                            {section name="birthyear" loop=2000 max=100 step=-1}
                                <option value="{$smarty.section.birthyear.index}"
                                        {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                            {/section}
                        </select>
                    </div>
                {/if}
                <input type="hidden" value="{$payment_mean.name}" name="payment_name_rate">
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Paymorrow_phone_div">
                        <label for="register_personal_phone"
                               id="Paymorrow_phone_label"><br/>{$pi_Paymorrow_lang['phone']['text']}</label>
                        <input id="register_personal_phone" class="text pi_Paymorrow_phone" type="text" value=""
                               name="register[personal][phone_rate]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || !$sUserData['billingaddress']['phone'] || $pi_Paymorrow_ustid || $pi_Paymorrow_company}
                    <input class="pi_Paymorrow_savebutton" name="pi_Paymorrow_saveBirthday_rate" type="submit"
                           value='{$pi_Paymorrow_lang['submit_value']}'/>
                {/if}
            </div>
        </div>
            {else}
            {$smarty.block.parent}
        {/if}
    {/block}

    {block name='frontend_register_payment_fieldset_input_radio'}
        {if ($pi_Paymorrow_no_Paymorrow || $sPaymorrowPaymentError || $sUserData.billingaddress.birthday=="0000-00-00" || !$sUserData['billingaddress']['phone'])&& ($payment_mean.name == "PaymorrowInvoice" || $payment_mean.name == "PaymorrowRate")}
        <div id="pi_Paymorrow_paymentWarning_register">
            <center>{$pi_Paymorrow_paymentWarningText}</center>
        </div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}"
                   id="payment_mean{$payment_mean.id}" disabled="disabled"/> <label class="description"
                                                                                    for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $payment_mean.name == "PaymorrowInvoice" && $pi_Paymorrow_invoice_surcharge}
                <span style="margin: 0 0 0 5px;">
                ({$pi_Paymorrow_lang['paymentfees']}:&nbsp;{$pi_Paymorrow_invoice_surcharge}&euro;) 
            </span>
                {elseif $payment_mean.name == "PaymorrowRate" && $pi_Paymorrow_rate_surcharge}
                <span style="margin: 0 0 0 5px;">
                ({$pi_Paymorrow_lang['paymentfees']}:&nbsp;{$pi_Paymorrow_rate_surcharge}&euro;) 
            </span>
            {/if}
        </div>
            {elseif !$sPaymorrowPaymentError && ($payment_mean.name == "PaymorrowInvoice" || $payment_mean.name == "PaymorrowRate")}
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}"
                   id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $form_data.payment or (!$form_data && !$smarty.foreach.register_payment_mean.index)}
                   checked="checked"{/if} /> <label class="description"
                                                    for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $payment_mean.name == "PaymorrowInvoice" && $pi_Paymorrow_invoice_surcharge}
                <span style="margin: 0 0 0 5px;">
                ({$pi_Paymorrow_lang['paymentfees']}:&nbsp;{$pi_Paymorrow_invoice_surcharge}&euro;) 
            </span>
                {elseif $payment_mean.name == "PaymorrowRate" && $pi_Paymorrow_rate_surcharge}
                <span style="margin: 0 0 0 5px;">
                ({$pi_Paymorrow_lang['paymentfees']}:&nbsp;{$pi_Paymorrow_rate_surcharge}&euro;) 
            </span>
            {/if}
        </div>
            {else}
            {$smarty.block.parent}
        {/if}
    {/block}

    {elseif $pi_Paymorrow_Viewport == "checkout"}
    {block name='frontend_checkout_payment_fieldset_description'}
        {if $payment_mean.name == "PaymorrowInvoice"}
        <div class="grid_10 last" id="Paymorrow_width_checkout">
            <img src="{link file='engine/Shopware/Plugins/Default/Frontend/PiPaymorrowPayment/img/checkout_Logo_ol_rechnung_small_trans.png' fullPath}"/>
            <br/><br/>
            {$payment_mean.additionaldescription}
            <div class="grid_8 bankdata" id="Paymorrow_invoicebirth" style="left: 0px;">
                {if $sUserData['billingaddress']['birthday']=='0000-00-00'}
                    <div id="birthdate" class="Paymorrow_birthday_div" style="left: 0px;">
                        <label for="register_personal_birthdate"
                               id="Paymorrow_birthday_label_rate" style="width: 170px;">{$pi_Paymorrow_lang['birthday']['text']}</label>
                        <select id="register_personal_birthdate" name="register[personal][birthday]">
                            <option value="">--</option>
                            {section name="birthdate" start=1 loop=32 step=1}
                                <option value="{$smarty.section.birthdate.index}"
                                        {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                            {/section}
                        </select>

                        <select name="register[personal][birthmonth]">
                            <option value="">-</option>
                            {section name="birthmonth" start=1 loop=13 step=1}
                                <option value="{$smarty.section.birthmonth.index}"
                                        {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                            {/section}
                        </select>

                        <select name="register[personal][birthyear]">
                            <option value="">----</option>
                            {section name="birthyear" loop=2000 max=100 step=-1}
                                <option value="{$smarty.section.birthyear.index}"
                                        {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                            {/section}
                        </select>
                    </div>
                {/if}
                <input type="hidden" value="{$payment_mean.name}" name="payment_name">
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Paymorrow_phone_div">
                        <label for="register_personal_phone"
                               id="Paymorrow_phone_label_rate"><br/>{$pi_Paymorrow_lang['phone']['text']}</label>
                        <input id="register_personal_phone" class="text pi_Paymorrow_phone" type="text" value=""
                               name="register[personal][phone]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || !$sUserData['billingaddress']['phone'] || $pi_Paymorrow_ustid || $pi_Paymorrow_company}
                    <input class="pi_Paymorrow_savebutton" name="pi_Paymorrow_saveBirthday" type="submit"
                           value='{$pi_Paymorrow_lang['submit_value']}'/>
                {/if}
            </div>
        </div>
            {elseif  $payment_mean.name == "PaymorrowRate"}
        <div class="grid_10 last" id="Paymorrow_width_checkout">
            <img src="{link file='engine/Shopware/Plugins/Default/Frontend/PiPaymorrowPayment/img/checkout_Logo_ol_raten_small_trans.png' fullPath}"/>
            <br/> <br/>
            {$payment_mean.additionaldescription}
            <div class="grid_8 bankdata" id="Paymorrow_invoicebirth" style="left: 0px;">
                {if $sUserData['billingaddress']['birthday']=='0000-00-00'}
                    <div id="birthdate" class="Paymorrow_birthday_div" style="left: 0px;">
                        <label for="register_personal_birthdate"
                               id="Paymorrow_birthday_label_rate" style="width: 170px;">{$pi_Paymorrow_lang['birthday']['text']}</label>
                        <select id="register_personal_birthdate" name="register[personal][birthday_rate]">
                            <option value="">--</option>
                            {section name="birthdate" start=1 loop=32 step=1}
                                <option value="{$smarty.section.birthdate.index}"
                                        {if $smarty.section.birthdate.index eq $form_data.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
                            {/section}
                        </select>

                        <select name="register[personal][birthmonth_rate]">
                            <option value="">-</option>
                            {section name="birthmonth" start=1 loop=13 step=1}
                                <option value="{$smarty.section.birthmonth.index}"
                                        {if $smarty.section.birthmonth.index eq $form_data.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
                            {/section}
                        </select>

                        <select name="register[personal][birthyear_rate]">
                            <option value="">----</option>
                            {section name="birthyear" loop=2000 max=100 step=-1}
                                <option value="{$smarty.section.birthyear.index}"
                                        {if $smarty.section.birthyear.index eq $form_data.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
                            {/section}
                        </select>
                    </div>
                {/if}
                <input type="hidden" value="{$payment_mean.name}" name="payment_name_rate">
                {if !$sUserData['billingaddress']['phone']}
                    <div id="phone" class="Paymorrow_phone_div">
                        <label for="register_personal_phone"
                               id="Paymorrow_phone_label_rate"><br/>{$pi_Paymorrow_lang['phone']['text']}</label>
                        <input id="register_personal_phone" class="text pi_Paymorrow_phone" type="text" value=""
                               name="register[personal][phone_rate]">
                    </div>
                {/if}
                {if $sUserData['billingaddress']['birthday']=='0000-00-00' || !$sUserData['billingaddress']['phone'] || $pi_Paymorrow_ustid || $pi_Paymorrow_company}
                    <input class="pi_Paymorrow_savebutton" name="pi_Paymorrow_saveBirthday_rate" type="submit"
                           value='{$pi_Paymorrow_lang['submit_value']}'/>
                {/if}
            </div>
        </div>
            {else}
            {$smarty.block.parent}
        {/if}
    {/block}

    {block name='frontend_checkout_payment_fieldset_input_radio'}
        {if ($pi_Paymorrow_no_Paymorrow || $sPaymorrowPaymentError || $sUserData.billingaddress.birthday=="0000-00-00" || !$sUserData['billingaddress']['phone'])&& ($payment_mean.name == "PaymorrowInvoice" || $payment_mean.name == "PaymorrowRate")}
        <div id="pi_Paymorrow_paymentWarning">
            <center>{$pi_Paymorrow_paymentWarningText}</center>
        </div>
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}"
                   id="payment_mean{$payment_mean.id}" disabled="disabled"/> <label class="description"
                                                                                    for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $payment_mean.name == "PaymorrowInvoice" && $pi_Paymorrow_invoice_surcharge}
                <span style="margin: 0 0 0 5px;">
                ({$pi_Paymorrow_lang['paymentfees']}:&nbsp;{$pi_Paymorrow_invoice_surcharge}&euro;) 
            </span>
                {elseif $payment_mean.name == "PaymorrowRate" && $pi_Paymorrow_rate_surcharge}
                <span style="margin: 0 0 0 5px;">
                ({$pi_Paymorrow_lang['paymentfees']}:&nbsp;{$pi_Paymorrow_rate_surcharge}&euro;) 
            </span>
            {/if}
        </div>
            {elseif !$sPaymorrowPaymentError && ($payment_mean.name == "PaymorrowInvoice" || $payment_mean.name == "PaymorrowRate")}
        <div class="grid_5 first">
            <input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}"
                   id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $sUserData.additional.payment.id}
                   checked="checked"{/if} /> <label class="description"
                                                    for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            {if $payment_mean.name == "PaymorrowInvoice" && $pi_Paymorrow_invoice_surcharge}
                <span style="margin: 0 0 0 5px;">
                    ({$pi_Paymorrow_lang['paymentfees']}:&nbsp;{$pi_Paymorrow_invoice_surcharge}&euro;) 
                </span>
                {elseif $payment_mean.name == "PaymorrowRate" && $pi_Paymorrow_rate_surcharge}
                <span style="margin: 0 0 0 5px;">
                    ({$pi_Paymorrow_lang['paymentfees']}:&nbsp;{$pi_Paymorrow_rate_surcharge}&euro;) 
                </span>
            {/if}
        </div>
            {else}
            {$smarty.block.parent}
        {/if}
    {/block}

{/if}