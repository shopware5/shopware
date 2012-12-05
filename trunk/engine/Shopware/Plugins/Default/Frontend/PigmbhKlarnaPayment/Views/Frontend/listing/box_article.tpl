{block name='frontend_listing_box_article_name' append}
{if $pi_klarna_rate_active && $piKlarnaArticles}
    <div class="KlarnaRateListingPrice">{$pi_Klarna_lang['rate']['from']} {$pi_klarna_rate[array_search($sArticle, $sArticles)]} {$piKlarnaShopCurrency}{$pi_Klarna_lang['rate']['value_month']|replace:$pi_Klarna_lang['currency']:""}
    (<a href="#" class="Klarna_partpayment" id="klarna_partpayment{$pi_klarna_counter[array_search($sArticle, $sArticles)]}" onclick="ShowKlarnaPartPaymentPopup();return false;"></a>)</div>
{elseif $pi_klarna_rate_active && $piKlarnaOffers}
    <div class="KlarnaRateListingPrice">{$pi_Klarna_lang['rate']['from']} {$pi_klarna_rate[array_search($sArticle, $sOffers)]} {$piKlarnaShopCurrency}{$pi_Klarna_lang['rate']['value_month']|replace:$pi_Klarna_lang['currency']:""}
    <br />(<a href="#" class="Klarna_partpayment" id="klarna_partpayment{$pi_klarna_counter[array_search($sArticle, $sOffers)]}" onclick="ShowKlarnaPartPaymentPopup();return false;"></a>)</div>
{/if}
{/block}