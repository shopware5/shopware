{if $PaypalShowButton}
<div class="modal_paypal_button right" style=" margin-right: 228px;">
    <a href="{url controller=payment_paypal action=express forceSecure}">
        <img src="https://www.paypal.com/{$PaypalLocale|default:'de_DE'}/i/btn/btn_xpressCheckout.gif">
    </a>
</div>
{/if}
