{extends file='frontend/index/index.tpl'}

{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
//<![CDATA[
	if(top!=self){
		top.location=self.location;
	}
//]]>
</script>
{/block}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name=PaymentTitle}Zahlung durchführen{/s}"]]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">

{if !empty($BillsafeResponse->ack) && $BillsafeResponse->ack == 'ERROR'}
    {if $BillsafeConfig.debug && $BillsafeResponse->errorList}
        <h2>{se name=PaymentDebugErrorMessage}Ein Fehler ist aufgetreten.{/se}</h2>
        {if $BillsafeResponse->errorList->message}
            <h2>[{$BillsafeResponse->errorList->code}] - {$BillsafeResponse->errorList->message|escape|nl2br}</h2>
        {/if}
    {else}
        <h2>{se name=PaymentErrorMessage}Es ist ein unbekannter Fehler aufgetreten und die Bestellung konnte nicht abgeschlossen werden.{/se}</h2>
    {/if}
    <br />
    <h3>{se name=PaymentErrorInfo}Bitte kontaktieren Sie den Shopbetreiber.{/se}</h3>
{elseif !empty($BillsafeResponse->status) && $BillsafeResponse->status == 'DECLINED'}
    {if $BillsafeConfig.debug && $BillsafeResponse->declineReason}
        <h2>[{$BillsafeResponse->declineReason->code}] - {$BillsafeResponse->declineReason->message|escape|nl2br}</h2>
    {/if}
    {if $BillsafeResponse->declineReason}
        <h2>{$BillsafeResponse->declineReason->buyerMessage|escape|nl2br}</h2>
    {/if}
    <br />
    <h3>{se name=PaymentFailInfo}Bitte versuchen Sie es mit einer anderen Zahlungsart nochmal.{/se}</h3>
{else}
    <h2>{se name=PaymentCancelMessage}Sie haben den Bezahlungsprozess abgebrochen.{/se}</h2>
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
{*block name='frontend_index_checkout_actions'}{/block*}
