{extends file="frontend/checkout/confirm.tpl"} {block
name='frontend_index_content_left'}{/block} {* Javascript *} {block
name="frontend_index_header_javascript" append}
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($) {
		$('#payment_frame').css('display', 'none');
		$('#payment_loader').css('display', 'block');
		
		$('#payment_frame').load(function(){
			$('#payment_loader').css('display', 'none');
			$('#payment_frame').css('display', 'block');
		});
	});
//]]>
</script>
{/block} {* Main content *} {block name="frontend_index_content"}
<div id="payment" class="grid_20"
	style="margin: 10px 0 10px 20px; width: 959px;">

<h2 class="headingbox_dark largesize">{se name="PaymentHeader"}Bitte schliessen Sie nun die Zahlung ab:{/se}</h2>
<center><iframe id="payment_frame" width="500px" frameborder="0"
	border="0" src="{$PaymentUrl}" style="width: 450px; border: 0px solid #000;"></iframe></center>
<div id="payment_loader" class="ajaxSlider"
	style="height: 100px; border: 0 none; display: none">
<div class="loader" style="width: 80px; margin-left: -50px;">{s
name="PaymentInfoWait"}Bitte warten...{/s}</div>
</div>

</div>
<div class="doublespace">&nbsp;</div>
{/block}
