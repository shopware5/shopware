{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='PaymentProcess' namespace='frontend/payment_heidelpay/success'}{/s}"]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">

<div>
<h2><img align="left" vspace="30" hspace="10" alt="Success" src="{link file='frontend/payment_heidelpay/img/success.png' fullPath}" style=" height: 50px; width: 50px;">
{s name='PaymentSuccess' namespace='frontend/payment_heidelpay/success'}{/s}</h2>
<br/>
<br/>
	
</div>
</div>
{/block}


{block name='frontend_index_actions'}{/block}
{block name='frontend_index_checkout_actions'}{/block}
{block name='frontend_index_search'}{/block}
