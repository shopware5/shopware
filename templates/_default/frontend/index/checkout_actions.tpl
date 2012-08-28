<div class="my_options">
	{block name="frontend_index_checkout_actions_account"}
		<a href="{url controller='account'}" title="{s name='IndexLinkAccount'}{/s}" class="account">
			{s name='IndexLinkAccount'}{/s}
		</a>
	{/block}
	{block name="frontend_index_checkout_actions_notepad"}
		<a href="{url controller='note'}" title="{s name='IndexLinkNotepad'}{/s}" class="note">
			{s name='IndexLinkNotepad'}{/s} ({$sNotesQuantity})
		</a>
	{/block}
	<div class="clear">&nbsp;</div>
</div>

<div id="shopnavi">
	<div class="grid_6 newbasket{if $sBasketQuantity} active{/if}">
		{block name="frontend_index_checkout_actions_cart"}
			<div class="grid_2 first icon">
				<a href="{url controller='checkout'}" title="{s name='IndexLinkCart'}{/s}">
					{if $sUserLoggedIn}{s name='IndexLinkCheckout'}{/s}{else}{s name='IndexLinkCart'}{/s}{/if}
				</a>
			</div>
			<div class="grid_5 first last display">
				<div class="top">
					<a href="{url controller='checkout' action='cart'}" title="{s name='IndexLinkCart'}{/s}" class="uppercase bold">{s name='IndexLinkCart'}{/s}</a>
					<div class="display_basket">
						<span class="quantity">{$sBasketQuantity} {s name='IndexInfoArticles'}{/s}</span>
						<span class="sep">|</span>
						<span class="amount">{$sBasketAmount|currency}*</span>
					</div>
				</div>
				
				<div class="ajax_basket_container hide_script">
					<div class="ajax_basket">
						{s name='IndexActionShowPositions'}{/s}
						
						{* Ajax loader *}
						<div class="ajax_loader">&nbsp;</div>
					</div>
				</div>
			</div>
		{/block}
		{block name="frontend_index_checkout_actions_inner"}{/block}
		<div class="clear">&nbsp;</div>
	</div>
</div>