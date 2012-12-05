{* todo@stp - add snippets *}
<div class="delivery-interval-container">
    <p>
        <label for="delivery-interval">Lieferinterval:</label>
        <select class="delivery-interval" id="delivery-interval">
            {for $deliveryInterval=$aboCommerce.minDeliveryInterval to $aboCommerce.maxDeliveryInterval}
                <option value="{$deliveryInterval}">
                    {$deliveryInterval}&nbsp;
                    {if $aboCommerce.deliveryIntervalUnit == "weeks"}
                        Woche(n)
                    {else}
                        Monat(e)
                    {/if}
                </option>
            {/for}
        </select>
    </p>

    <p>
        <label for="duration-interval">Laufzeit:</label>
        <select class="duration-interval" id="duration-interval">
            {for $durationInterval=$aboCommerce.minDuration to $aboCommerce.maxDuration}
                <option value="{$durationInterval}">
                    {$durationInterval}&nbsp;
                    {if $aboCommerce.durationUnit == "weeks"}
                        Woche(n)
                    {else}
                        Monat(e)
                    {/if}
                </option>
            {/for}
        </select>
    </p>
</div>