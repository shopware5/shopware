{block name='frontend_detail_data_price_info' prepend}
{if $pi_klarna_rate_active}
	<div class="KlarnaShowRateDiv">
	    <span class="Klarna_rate_span">{$pi_Klarna_lang['rate']['from_amount']} {$pi_klarna_rate} {$piKlarnaShopCurrency}{$pi_Klarna_lang['rate']['value_month']|replace:$pi_Klarna_lang['currency']:""}</span><br />
	    (<a class="Klarnacolor" href="#" title="{$pi_Klarna_lang['rate']['href']}" id="klarna_partpayment" onclick="ShowKlarnaPartPaymentPopup();return false;">{$pi_Klarna_lang['rate']['read_more']}</a>)
	    <a class="Klarnacolor" href="https://klarna.com/de/privatpersonen/unsere-services/klarna-ratenkauf" title="{$pi_Klarna_lang['rate']['href']}" target="_blank">
	        <img src="{$piKlarnaImgDir|cat:'KlarnaRatepayLogo.png'}" class="KlarnaRateSmallImg"/>
	    </a>
	    <br /><br /><br />
    </div>
{/if}
{/block}