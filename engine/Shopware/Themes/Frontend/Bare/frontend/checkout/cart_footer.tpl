{block name='frontend_checkout_cart_cart_footer_add_product'}

	{* Add product using the sku *}
	<form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}" class="table--add-product add-product--form block-group">

		{block name='frontend_checkout_cart_cart_footer_add_product_field'}
			<input name="sAdd" class="add-product--field block" type="text" placeholder="{s name='CheckoutFooterAddProductPlaceholder' namespace='frontend/checkout/cart_footer_left'}{/s}" />
		{/block}

		{block name='frontend_checkout_cart_cart_footer_add_product_button'}
			<button type="submit" class="add-product--button btn btn--primary is--small block">
				<i class="icon--arrow-right"></i>
			</button>
		{/block}
	</form>
{/block}