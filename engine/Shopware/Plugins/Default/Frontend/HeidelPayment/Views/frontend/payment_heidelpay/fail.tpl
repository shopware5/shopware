{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='PaymentProcess' namespace='frontend/payment_heidelpay/fail'}{/s}"]}
{/block}

{block name='frontend_index_header' append}
<script type="text/javascript">
//<![CDATA[
if(top!=self){
top.location=self.location;
}
//]]>
</script>
{/block}


{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">

<div>
<h2><img align="left" vspace="10" hspace="20" alt="Warnung" src="{link file='frontend/payment_heidelpay/img/exclamation_mark.png'}" style=" height: 50px; width: 50px;">
{s name='PaymentFailed' namespace='frontend/payment_heidelpay/fail'}{/s}</h2>
	<h3 style="color: #d62727 ;">- {$ErrorMessage}</h3>
</div>
{if $back2basket}
<div class="actions">
	<br />
	<br />
	<br />
	<a class="button-left large left" href="{url controller=checkout action=cart}" title="{s name='basket' namespace='frontend/payment_heidelpay/fail'}{/s}">
		{s name='basket' namespace='frontend/payment_heidelpay/fail'}{/s}
	</a>
</div>
{/if}
</div>
{/block}


{block name='frontend_index_actions'}{/block}
{block name='frontend_index_checkout_actions'}{/block}
{block name='frontend_index_search'}{/block}
