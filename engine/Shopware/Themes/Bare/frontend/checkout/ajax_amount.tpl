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

<div class="ajax_basket_container">
    <div class="ajax_basket">
        {s namespace='frontend/index/checkout_actions' name='IndexActionShowPositions'}{/s}
        {* Ajax loader *}
        <div class="ajax_loader">&nbsp;</div>
    </div>
</div>

{if $sBasketQuantity > 0}
    <a href="{url controller='checkout' action='cart'}" class="quantity">{$sBasketQuantity}</a>
{/if}