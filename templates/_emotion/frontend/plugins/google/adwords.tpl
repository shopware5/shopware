{if $GoogleConversionID}

{$sRealAmount=$sAmount|replace:",":"."}
{if $sAmountWithTax}
	{$sRealAmount=$sAmountWithTax|replace:",":"."}
{/if}
<script type="text/javascript">
    var google_conversion_id = "{$GoogleConversionID}";
        google_conversion_language = "{$GoogleConversionLanguage}";
        google_conversion_format = "1";
        google_conversion_color = "FFFFFF";
        google_conversion_value = parseInt('{$sRealAmount}', 10);
        google_conversion_label = "purchase";
</script>
<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
	<img height=1 width=1 border=0 src="https://www.googleadservices.com/pagead/conversion/{$GoogleConversionID}/imp.gif?value={$sRealAmount}&label=purchase&script=0">
</noscript>
{/if}
