
{if $GoogleConversionID}
{if $sAmountWithTax}
	{assign var="sRealAmount" value=$sAmountWithTax|replace:",":"."}
{else}
	{assign var="sRealAmount" value=$sAmount|replace:",":"."}
{/if}
<script type="text/javascript">
//<![CDATA[
    var google_conversion_id = "{$GoogleConversionID}";
    var google_conversion_language = "{$GoogleConversionLanguage}";
    var google_conversion_format = "1";
    var google_conversion_color = "FFFFFF";
    var google_conversion_value = {$sRealAmount};
    var google_conversion_label = "purchase";
//]]>
</script>
<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
	<img height=1 width=1 border=0 src="https://www.googleadservices.com/pagead/conversion/{$GoogleConversionID}/imp.gif?value={$sRealAmount}&label=purchase&script=0">
</noscript>
{/if}
