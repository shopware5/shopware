<div class="grid_9 box">
	{* Add voucher *}
	{block name='frontend_checkout_table_footer_left_add_voucher'}
		<div class="vouchers">
			<form method="post" action="{url action='addVoucher' sTargetAction=$sTargetAction}">
				<input type="text" class="text" id="basket_add_voucher" name="sVoucher" onfocus="this.value='';" value="{s name='CheckoutFooterAddVoucherLabelInline'}{/s}" />
				<input type="submit" value="{s name='CheckoutFooterActionAddVoucher'}{/s}" class="box_send" />
			</form>
		</div>

		<div class="add_article">
			<form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}">
				<input id="basket_add_article" name="sAdd" type="text" value="{s name='CheckoutFooterIdLabelInline'}{/s}" onfocus="this.value='';" class="ordernum text" />
				<input type="submit" class="box_send" value="{s name='CheckoutFooterActionAdd'}{/s}" />
			</form>
		</div>

		{* Deliveryfree *}
		{if $sShippingcostsDifference}
			<div class="box_cart_info">
			<p>
				<strong>{se name="CartInfoFreeShipping" namespace="frontend/checkout/cart"}{/se}</strong>
				{se name="CartInfoFreeShippingDifference" namespace="frontend/checkout/cart"}{/se}
			</p>
			</div>
		{/if}
	{/block}
	
	<div class="clear"></div>
	
	{* Add article with order number *}
	{block name='frontend_checkout_table_footer_left_add_article'}{/block}
</div>