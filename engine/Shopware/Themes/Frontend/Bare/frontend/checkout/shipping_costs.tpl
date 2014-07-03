<form class="table--shipping-costs{if !$calculateShippingCosts} is--hidden{/if}" method="POST" action="{url action='calculateShippingCosts' sTargetAction=$sTargetAction}">

    {* Delivery country *}
    {block name='frontend_checkout_shipping_costs_country'}
        <div class="shipping-costs--country">
            <label for="basket_country_list">{s name="ShippingLabelDeliveryCountry"}{/s}</label>
            <select id="basket_country_list" name="sCountry" data-auto-submit-form="true">
                {foreach from=$sCountryList item=country}
                    <option value="{$country.id}" {if $country.id eq $sCountry.id}selected{/if}>
                        {$country.countryname}
                    </option>
                {/foreach}
            </select>
        </div>

        {* County state selection *}
        {block name='frontend_checkout_shipping_costs_country'}
            {foreach $sCountryList as $country}
                {if $country.states}
                    <div class="shipping-costs--states{if $country.id != $sCountry.id} is--hidden{/if}">
                        <label for="country_{$country.id}_states">{s name='RegisterBillingLabelState'}{/s}</label>
                        <select name="sState" id="country_{$country.id}_states" data-auto-submit-form="true"{if $country.id != $sCountry.id} disabled="disabled"{/if}>
                            <option value="" selected="selected">{s name='StateSelection'}{/s}</option>
                            {foreach from=$country.states item=state}
                                <option value="{$state.id}" {if $state.id eq $sState.id || $state.id eq $sState}selected="selected"{/if}>
                                    {$state.name}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                {/if}
            {/foreach}
        {/block}
    {/block}

    {* Payment method *}
    {block name='frontend_checkout_shipping_costs_payment'}
        <div class="shipping-costs--payment">
            <label for="basket_payment_list">{s name="ShippingLabelPayment"}{/s}</label>

            <select id="basket_payment_list" name="sPayment" data-auto-submit-form="true">
                {foreach from=$sPayments item=payment}
                    <option value="{$payment.id}" {if $payment.id eq $sPayment.id}selected="selected"{/if}>
                        {$payment.description}
                    </option>
                {/foreach}
            </select>
        </div>
    {/block}

    {* Dispatch method *}
    {block name='frontend_checkout_shipping_costs_dispatch'}
        <div class="shipping-costs--dispatch">
            <label for="basket_dispatch_list">{s name="ShipppingLabelDispatch"}{/s}</label>
            <select id="basket_dispatch_list" name="sDispatch" data-auto-submit-form="true">
            {if $sDispatches}
                {foreach from=$sDispatches item=dispatch}
                    <option value="{$dispatch.id}" {if $dispatch.id eq $sDispatch.id}selected="selected"{/if}>
                        {$dispatch.name}
                    </option>
                {/foreach}
            {/if}
            </select>
        </div>
    {/block}

    {* Dispatch notice *}
    {block name='frontend_checkout_shipping_costs_dispatch_notice'}
        {if $sDispatch.description}
            <p class="dispatch--notice">
                {$sDispatch.description}
            </p>
        {/if}
    {/block}

</form>
