{block name='frontend_checkout_error_messages_basket_error' prepend}
    {if $sBundleValidation|@count > 0}
        <div class="error center bold">
            {foreach $sBundleValidation as $validation}
                {if $validation.noStock == 1}
                    <p>
                        {s namespace="frontend/checkout/bundle" name="CheckoutBundleError"}Bundle{/s} - {$validation.bundle}: {s name="CheckoutBundleErrorNoStock"}Kein Lagerbestand verfügbar{/s}
                    </p>
                {/if}
                {if $validation.notForCustomerGroup == 1}
                    <p>
                        {s namespace="frontend/checkout/bundle" name="CheckoutBundleError"}Bundle{/s} - {$validation.bundle}: {s namespace="frontend/checkout/bundle" name="CheckoutBundleErrorCustomerGroup"}Nicht für Ihre Kundengruppe freigegeben{/s}
                    </p>
                {/if}

                {if $validation.noArticleStock == 1}
                    {s namespace="frontend/checkout/bundle" name="CheckoutBundleArticleError"}Artikel{/s} - {$validation.article}: {s namespace="frontend/checkout/bundle" name="CheckoutBundleErrorNoStock"}Kein Lagerbestand mehr verfügbar{/s}
                {/if}
                {if $validation.articleNotForCustomerGroup == 1}
                    <p>
                        {s namespace="frontend/checkout/bundle" name="CheckoutBundleArticleError"}Artikel{/s} - {$validation.article}: {s namespace="frontend/checkout/bundle" name="CheckoutBundleErrorCustomerGroup"}Nicht für Ihre Kundengruppe freigegeben{/s}
                    </p>
                {/if}
            {/foreach}
        </div>
    {/if}
{/block}