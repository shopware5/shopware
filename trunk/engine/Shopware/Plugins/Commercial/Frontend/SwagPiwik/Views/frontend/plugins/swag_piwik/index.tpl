{block name='frontend_index_header_javascript' append}
{literal}
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://{/literal}{$SwagPiwik.p_url}{literal}" : "http://{/literal}{$SwagPiwik.p_url}{literal}");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", {/literal}{$SwagPiwik.p_ID}{literal});
{/literal}

{* ----- TRACKING DETAIL ----- *}
{if $Controller == "detail"}
piwikTracker.setEcommerceView("{$sArticle.ordernumber}","{$sArticle.articleName}","{$sCategoryInfo.name}");
{/if}

{* ----- TRACKING CATEGORY ----- *}
{if $Controller == "listing"}
piwikTracker.setEcommerceView(
productSku = false,productName = false,category = "{$sCategoryInfo.description}");
{/if}

{* ----- TRACKING ORDERS WITH ARTICLES ----- *}
{if $sBasket.content && $sOrderNumber}
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
      {assign var="sAmountTax2" value=$sAmountTax-$sAmountNumeric}
      {assign var="sAmountshipping1" value=$sShippingcosts|replace:",":"."}
      {assign var="sAmountsubtotal" value=$sAmountTax-$sAmountshipping1}
 
{foreach from=$sBasket.content item=sBasketItem}{if !$sBasketItem.modus}
piwikTracker.addEcommerceItem(
   "{$sBasketItem.ordernumber}", "{$sBasketItem.articlename|escape:'javascript'}", "&nbsp;", {$sBasketItem.priceNumeric|round:2}, {$sBasketItem.quantity}
   );   
{/if}{/foreach}
 
piwikTracker.trackEcommerceOrder(
"{$sOrderNumber}",
"{$sAmountTax|round:2}",
"{$sAmountsubtotal|round:2}", // (optional) Order sub total (excludes shipping)
"{$sAmountTax2|round:2}", // (optional) Tax amount
"{$sShippingcosts|replace:',':'.'|round:2}",
false // (optional) Discount offered (set to false for unspecified parameter)
);
{/if}

{* ----- TRACKING CART AND ADDING ARTICLES TO CART ----- *}
{if $Controller == "checkout" && !$sOrderNumber}
{foreach from=$sBasket.content item=sBasketItem}{if !$sBasketItem.modus}
piwikTracker.addEcommerceItem(
	"{$sBasketItem.ordernumber}", "{$sBasketItem.articlename|escape:'javascript'}", "-", {$sBasketItem.priceNumeric|round:2}, {$sBasketItem.quantity}
	);	
{/if}{/foreach}
piwikTracker.trackEcommerceCartUpdate({$sAmount});
{/if}

{literal}
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://{/literal}{$SwagPiwik.p_url}{literal}piwik.php?idsite={/literal}{$SwagPiwik.p_ID}{literal}" style="border:0" alt="" /></p></noscript>
{/literal}
{/block}