<div class="grid_9 box">
	{* Add voucher *}
	{block name='frontend_checkout_table_footer_left_add_voucher'}
	<div class="vouchers">
		<form method="post" action="{url action='addVoucher' sTargetAction=$sTargetAction}">
			<label for="basket_add_voucher">{s name="CheckoutFooterLabelAddVoucher"}{/s}</label>
			<input type="text" class="text" id="basket_add_voucher" name="sVoucher" onfocus="this.value='';" value="{s name='CheckoutFooterAddVoucherLabelInline'}{/s}" />
			<input type="submit" value="{s name='CheckoutFooterActionAddVoucher'}{/s}" class="button_tablefoot" />
		</form>
	</div>
	{/block}
	
	<hr class="clear" />
	
	{* Add article with order number *}
	{block name='frontend_checkout_table_footer_left_add_article'}
	<div class="add_article">
		<form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}">
			<label for="basket_add_article">{s name='CheckoutFooterLabelAddArticle'}{/s}:</label>
			<input id="basket_add_article" name="sAdd" type="text" value="{s name='CheckoutFooterIdLabelInline'}{/s}" onfocus="this.value='';" class="ordernum text" />
			<input type="submit" class="button_tablefoot" value="{s name='CheckoutFooterActionAdd'}{/s}" />
		</form>
	</div>
	{/block}
</div>