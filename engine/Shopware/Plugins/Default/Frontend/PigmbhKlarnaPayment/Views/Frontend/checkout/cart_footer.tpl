{block name='frontend_checkout_cart_footer_tax_rates' append}
</div>
{if $pi_klarna_rate_active}
<div class="KlarnaShowRateDiv KlarnaFloatRight">
    <span class="Klarna_rate_span">{$pi_Klarna_lang['rate']['from_amount']} {$pi_klarna_rateAmount} {$pi_Klarna_lang['rate']['value_month']}</span><br />
    (<a class="Klarnacolor" href="#" title="{$pi_Klarna_lang['rate']['href']}" id="klarna_partpayment" onclick="ShowKlarnaPartPaymentPopup();return false;">{$pi_Klarna_lang['rate']['read_more']}</a>)
    <a href="https://klarna.com/de/privatpersonen/unsere-services/klarna-ratenkauf" title="{$pi_Klarna_lang['rate']['href']}" target="_blank">
        <img src="{$piKlarnaImgDir|cat:'KlarnaRatepayLogo.png'}" class="KlarnaRateSmallImg"/>
    </a>
{/if}
{/block}