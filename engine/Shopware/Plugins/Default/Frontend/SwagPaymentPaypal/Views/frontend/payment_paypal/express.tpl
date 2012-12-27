{if $PaypalShowButton}
<div class="basket_bottom_paypal">
    <a href="{url controller=payment_paypal action=express forceSecure}">
        <img src="https://www.paypal.com/{$PaypalLocale|default:'de_DE'}/i/btn/btn_xpressCheckout.gif">
    </a>
</div>
{/if}
