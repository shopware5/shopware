
{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
	{assign var='sBreadcrumb' value=[['name'=>'Paypal Express Order Pending Page'|snippet:'PalpalPendingTitle']]}
{/block}

{block name='frontend_index_content'}
<div class="grid_16" id="center">
	<h2>{se name="PalpalPendingTitle"}Vielen Dank für Ihre Bestellung.{/se}</h2>
	<div>
		{s name="PalpalPendingInfo"}
		<p>
		Sobald Ihre Überweisung bei PayPal eingegangen ist, werden wir informiert und verschicken die Ware dann umgehend.<br /><br />
		Sie haben den Betrag noch nicht Überwiesen? Kein Problem. Die Bankverbindung von PayPal können Sie jederzeit in Ihrem PayPal-Konto abrufen.
		Klicken Sie in der Kontoübersicht direkt neben der Zahlung auf den Link "Details".
		Auf der nächsten Seite finden Sie unter dem Link "So schließen Sie Ihre PayPal-Zahlung per Banküberweisung ab" alle nötigen Informationen.
		</p>
		{/s}
	</div>
	<a href="{url controller='index'}" title="{s name='PalpalPendingLinkHomepage'}{/s}" class="button-left large modal_close">
		{se name="PalpalPendingLinkHomepage"}{/se}
	</a>
</div>
{/block}
