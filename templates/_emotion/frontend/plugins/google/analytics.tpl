
<script type="text/javascript">
//<![CDATA[
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', "{$GoogleTrackingID|escape:'javascript'}"]);
{if $GoogleAnonymizeIp}
	_gaq.push(['_gat._anonymizeIp']);
{/if}
	_gaq.push(['_trackPageview']);
	
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
		{assign var="sAmountTax" value=$sAmountTax-$sAmountNumeric}
		
		_gaq.push(['_addTrans',
			"{$sOrderNumber|round}",
			"{$sShopname|escape:'javascript'}",
			"{$sAmountNumeric|round:2}",
			"{$sAmountTax|round:2}",
			"{$sShippingcosts|replace:',':'.'|round:2}",
			"{$sUserData.billingaddress.city|escape}",
			"",
			"{$sUserData.additional.country.countryen|escape}"
		]);
		
{foreach from=$sBasket.content item=sBasketItem}{if !$sBasketItem.modus}
		_gaq.push(['_addItem',
			"{$sOrderNumber|round}",
			"{$sBasketItem.ordernumber}",
			"{$sBasketItem.articlename|escape:'javascript'}",
			"",
			"{$sBasketItem.priceNumeric|round:2}",
			"{$sBasketItem.quantity|round}"
		]);
{/if}{/foreach}
		
		_gaq.push(['_trackTrans']);
	{/if}
	{literal}
		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
		})();
	{/literal}
//]]>
</script>
