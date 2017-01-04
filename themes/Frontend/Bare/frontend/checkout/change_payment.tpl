<div class="payment--method-list panel has--border is--rounded block">

    {block name='frontend_checkout_payment_headline'}
        <h3 class="payment--method-headline panel--title is--underline">{s namespace='frontend/checkout/shipping_payment' name='ChangePaymentTitle'}{/s}</h3>
    {/block}

    {block name='frontend_checkout_payment_content'}
        <div class="panel--body is--wide block-group">
            {foreach $sPayments as $payment_mean}
                <div class="payment--method block{if $payment_mean@last} method_last{else} method{/if}">

                    {* Radio Button *}
                    {block name='frontend_checkout_payment_fieldset_input_radio'}
                        <div class="method--input">
                            <input type="radio" name="payment" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $sFormData.payment or (!$sFormData && !$smarty.foreach.register_payment_mean.index)} checked="checked"{/if} />
                        </div>
                    {/block}

                    {* Method Name *}
                    {block name='frontend_checkout_payment_fieldset_input_label'}
                        <div class="method--label is--first">
                            <label class="method--name is--strong" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
                        </div>
                    {/block}

                    {* Method Description *}
                    {block name='frontend_checkout_payment_fieldset_description'}
                        <div class="method--description is--last">
                            {include file="string:{$payment_mean.additionaldescription}"}
                        </div>
                    {/block}

                    {* Method Logo *}
                    {block name='frontend_checkout_payment_fieldset_template'}
                        <div class="payment--method-logo payment_logo_{$payment_mean.name}"></div>
                        {if "frontend/plugins/payment/`$payment_mean.template`"|template_exists}
                            <div class="method--bankdata{if $payment_mean.id != $form_data.payment} is--hidden{/if}">
                                {include file="frontend/plugins/payment/`$payment_mean.template`" form_data=$sFormData error_flags=$sErrorFlag payment_means=$sPayments}
                            </div>
                        {/if}
                    {/block}
                </div>
            {/foreach}
        </div>
    {/block}
</div>