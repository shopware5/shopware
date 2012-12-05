{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='PaymentProcess' namespace='frontend/payment_heidelpay/prepayment'}{/s}"]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">

<div>
<h2><img align="left" vspace="30" hspace="10" alt="Warnung" src="{link file='frontend/payment_heidelpay/img/success.png'}" style=" height: 50px; width: 50px;">
{s name='PaymentSuccess' namespace='frontend/payment_heidelpay/prepayment'}{/s}</h2>
<br/>
<br/>
	{$bankInfo}
</div>
{if $back2basket}
<div class="actions">
	<br />
	<br />
	<br />
	<a class="button-right large right" href="{url controller=checkout action=finish sUniqueID=$transID}" title="{s name='OrderOverview' namespace='frontend/payment_heidelpay/prepayment'}{/s}">
		{s name='OrderOverview' namespace='frontend/payment_heidelpay/prepayment'}{/s}
	</a>
</div>
{/if}
</div>
{/block}


{block name='frontend_index_actions'}{/block}
{block name='frontend_index_checkout_actions'}{/block}
{block name='frontend_index_search'}{/block}
