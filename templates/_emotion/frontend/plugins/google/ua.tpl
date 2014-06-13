<script type="text/javascript">
    //<![CDATA[

    // Disable tracking if the opt-out cookie exists.
    var disableStr = "ga-disable-{$GoogleTrackingID|escape:'javascript'}";
    if (document.cookie.indexOf(disableStr + '=true') > -1) {
        window[disableStr] = true;
    }

    // Opt-out function
    function gaOptout() {
        document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
        window[disableStr] = true;
    }

    (function() {
        {literal}
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        {/literal}

        ga('create', "{$GoogleTrackingID|escape:'javascript'}", 'auto');
        {if $GoogleAnonymizeIp}
        ga('set', 'anonymizeIp', true);
        {/if}

        {if $sBasket.content && $sOrderNumber}
        ga('require', 'ecommerce', 'ecommerce.js');

        {if $sAmountWithTax}
        {assign var="sAmountTax" value=$sAmountWithTax|replace:",":"."}
        {else}
        {assign var="sAmountTax" value=$sAmount|replace:",":"."}
        {/if}

        {if $sAmountNet}
        {assign var="sAmountNumeric" value=$sAmountNet|replace:",":"."}
        {else}
        {assign var="sAmountNumeric" value=$sAmount|replace:",":"."}
        {/if}
        {assign var="sAmountTax" value=$sAmountTax-$sAmountNumeric}

        ga('ecommerce:addTransaction', {
            'id': '{$sOrderNumber|round}',
            'affiliation': '{$sShopname|escape:'javascript'}',
            'revenue': '{$sAmountNumeric|round:2}',
            'tax': '{$sAmountTax|round:2}',
            'shipping': '{$sShippingcosts|replace:',':'.'|round:2}',
            'currency': '{$sBasket.sCurrencyName}'
        });

        {foreach from=$sBasket.content item=sBasketItem}
        {if !$sBasketItem.modus}
        ga('ecommerce:addItem', {
            'id': '{$sOrderNumber|round}',
            'name': '{$sBasketItem.articlename|escape:'javascript'}',
            'sku': '{$sBasketItem.ordernumber}',
            'price': '{$sBasketItem.priceNumeric|round:2}',
            'quantity': '{$sBasketItem.quantity|round}'
        });
        {/if}
        {/foreach}

        ga('ecommerce:send');
        {/if}

        ga('send', 'pageview');
    })();
    //]]>
</script>
