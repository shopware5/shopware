{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name=PaymentTitle}Zahlung durchführen{/s}"]]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">

{if !empty($PaypalResponse.ACK) && $PaypalResponse.ACK == 'Failure'
  && ($PaypalConfig.paypalSandbox || $PaypalConfig.paypalErrorMode)}
    <h2>{se name=PaymentDebugErrorMessage}Ein Fehler ist aufgetreten.{/se}</h2>
    {$i=0}{while isset($PaypalResponse["L_LONGMESSAGE{$i}"])}
        <h3>[{$PaypalResponse["L_ERRORCODE{$i}"]}] - {$PaypalResponse["L_SHORTMESSAGE{$i}"]|escape|nl2br} {$PaypalResponse["L_LONGMESSAGE{$i}"]|escape|nl2br}</h3>
    {$i=$i+1}{/while}
{else}
    <h2>{se name=PaymentErrorMessage}Es ist ein Problem aufgetreten und die Bestellung konnte nicht abgeschlossen werden.{/se}</h2>
    <br />
    <h3>{se name=PaymentErrorInfo}Bitte kontaktieren Sie den Shopbetreiber.{/se}</h3>
{/if}

<br />

<div class="actions">
	<a class="button-left large left" href="{url controller=checkout action=cart}" title="{s name=PaymentLinkChangeBasket}Warenkorb ändern{/s}">
		{se name=PaymentLinkChangeBasket}{/se}
	</a>
	<a class="button-right large right" href="{url controller=account action=payment sTarget=checkout sChange=1}" title="{s name=PaymentLinkChange}Zahlungsart ändern{/s}">
		{se name=PaymentLinkChange}{/se}
	</a>
</div>

</div>
{/block}

{block name='frontend_index_actions'}{/block}
