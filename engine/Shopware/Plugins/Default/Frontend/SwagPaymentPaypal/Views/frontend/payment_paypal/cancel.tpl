{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name=PaymentTitle}Zahlung durchführen{/s}"]]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">
	<h2>{se name=PaymentCancelMessage}Sie haben den Bezahlungsprozess abgebrochen.{/se}</h2>
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
