{block name='frontend_checkout_error_messages_basket_error' prepend}
    {if $sLiveShoppingValidation|@count > 0}
        <div class="error center bold liveshopping_error">
            {foreach $sLiveShoppingValidation as $validation}
                <p>
                    {if $validation.noLiveShoppingDetected == 1}
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingError"}Liveshopping Aktion{/s} {if $validation.article} - {$validation.article}: {/if}<br>
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingDetected"}Aktion konnte nicht ermittelt werden.{/s}
                        <a href="{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}">
                            {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}
                        </a>
                    {/if}

                    {if $validation.noMoreActive == 1}
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingEnded"}Aktion ist ausgelaufen.{/s}
                        <a href="{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}">
                            {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}
                        </a>
                    {/if}

                    {if $validation.notForCurrentCustomerGroup == 1}
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingCustomerGroup"}Aktion ist für Ihre Kundengruppe nicht freigeschaltet.{/s}
                        <a href="{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}">
                            {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}
                        </a>
                    {/if}

                    {if $validation.noStock == 1}
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingStock"}Aktion ist nicht mehr auf Lager.{/s}
                        <a href="{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}">
                            {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}
                        </a>
                    {/if}

                    {if $validation.notForShop == 1}
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingShop"}Aktion nicht für den sub shop frei gegeben.{/s}
                        <a href="{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}">
                            {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}
                        </a>
                    {/if}

                    {if $validation.outOfDate == 1}
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>
                        {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingEnded"}Aktion ist ausgelaufen.{/s}
                        <a href="{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}">
                            {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}
                        </a>
                    {/if}
                </p>
            {/foreach}
        </div>
    {/if}
{/block}