<div class="shipping_method">
    <h3 class="headingbox_dark largesize">{s namespace='frontend/checkout/shipping_payment' name='ChangeShippingTitle'}{/s}</h3>

    {foreach $sDispatches as $dispatch}
        <div class="grid_15 method">
            {block name='frontend_checkout_dispatch_shipping_input_radio'}
                <div class="grid_5 first">
                    <input type="radio" id="confirm_dispatch{$dispatch.id}" class="radio auto_submit" value="{$dispatch.id}" name="sDispatch" {if $dispatch.id eq $sDispatch.id}checked="checked"{/if} />
                    <label class="description" for="confirm_dispatch{$dispatch.id}">{$dispatch.name}</label>
                </div>
            {/block}

            {block name='frontend_checkout_shipping_fieldset_description'}
                {if $dispatch.description}
                    <div class="grid_10 last">
                        {$dispatch.description}
                    </div>
                {/if}
            {/block}
        </div>
    {/foreach}

    {block name="frontend_checkout_shipping_action_buttons"}
        <input type="hidden" class="agb-checkbox" name="sAGB" value="{if $sAGBChecked}1{else}0{/if}" />
    {/block}
    <div class="clear">&nbsp;</div>
</div>
<div class="space"></div>