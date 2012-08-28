<div class="my_options">
	{block name="frontend_index_checkout_actions_notepad"}
		
			<a href="{url controller='account'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}" class="account">
				{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}
			</a>

	
		<a href="{url controller='note'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkNotepad'}{/s}" {if $sNotesQuantity > 0}style="padding-right: 25px;"{/if} class="note">
			{s namespace='frontend/index/checkout_actions' name='IndexLinkNotepad'}{/s} {if $sNotesQuantity > 0}<span class="notes_quantity">{$sNotesQuantity}</span>{/if}
		</a>
		<span class="service">
			<span>{s name='IndexLinkService'}Service/Hilfe{/s}</span>
            {action module=widgets controller=index action=menu group=gLeft}
		</span>
		{* Language and Currency bar *}
        {action module=widgets controller=index action=shopMenu}
	{/block}
    <div class="clear">&nbsp;</div>
</div>

<div id="shopnavi">
    
	{block name="frontend_index_checkout_actions_cart"}
    <div class="grid_6 newbasket{if $sBasketQuantity} active{/if}">
    
		<div class="grid_2 last icon">
			<a href="{url controller='checkout' action='cart'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkCart'}{/s}">
				{if $sUserLoggedIn}{s name='IndexLinkCheckout'}{/s}{else}{s namespace='frontend/index/checkout_actions' name='IndexLinkCart'}{/s}{/if}
			</a>
		</div>

		<div class="grid_5 first display">
			<div class="basket_left">
				<span>
					<a href="{url controller='checkout' action='cart'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkCart'}{/s}">
						{s namespace='frontend/index/checkout_actions' name='IndexLinkCart'}{/s}
					</a>
				</span>
			</div>
			<div class="basket_right">
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

		{if $sBasketQuantity > 0}
			<a href="{url controller='checkout' action='cart'}" class="quantity">{$sBasketQuantity}</a>
		{/if}
		
        <div class="clear">&nbsp;</div>
    </div>
	{/block}
	
    {block name="frontend_index_checkout_actions_inner"}{/block}
    
</div>