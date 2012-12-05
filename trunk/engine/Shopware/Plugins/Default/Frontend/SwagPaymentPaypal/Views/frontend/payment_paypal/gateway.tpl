{extends file='frontend/index/index.tpl'}

{block name='frontend_index_content_left'}{/block}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name=PaymentTitle}Zahlung mit PayPal{/s}"]]}
{/block}

{* Main content *}
{block name="frontend_index_content"}
<div id="payment" class="grid_20" style="margin:10px 0 10px 20px;width:959px;">

	<h2 class="headingbox_dark largesize">{se name="PaymentHeader"}&nbsp;{/se}</h2>
    <div id="payment_loader" class="ajaxSlider" style="height:100px;border:0 none;">
    	<div class="loader" style="width:80px;margin-left:-50px;">{s name="PaymentInfoWait"}Bitte warten...{/s}</div>
    </div>

</div>
<div class="doublespace">&nbsp;</div>
{/block}

{block name="frontend_index_header_meta_http_tags" append}
	<meta http-equiv="refresh" content="0; url={$PaypalGatewayUrl}">
{/block}
