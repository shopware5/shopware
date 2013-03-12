{extends file='parent:frontend/checkout/cart.tpl'}

{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="grid_16 last" id="basket">

	{* If articles are in the basket... *}
	{if $sBasket.content}


		{* Add article informations *}
		{block name='frontend_checkout_add_article'}
			<noscript>
				{include file='frontend/checkout/added.tpl'}
			</noscript>
		{/block}

		{* Error messages *}
		{block name='frontend_checkout_cart_error_messages'}
			{include file="frontend/checkout/error_messages.tpl"}
		{/block}


		{block name='frontend_checkout_cart_deliveryfree'}{/block}

			<div class="table grid_16 cart">
			{* Checkout *}
			<div class="actions">
				{block name="frontend_checkout_actions_confirm"}
				{if !$sMinimumSurcharge && !$sDispatchNoOrder}
				    <a href="{url action=confirm}" title="{s name='CheckoutActionsLinkProceed' namespace="frontend/checkout/actions"}{/s}" class="button-right large right checkout" >
						{se name="CheckoutActionsLinkProceed" namespace="frontend/checkout/actions"}{/se}
				    </a>
				    <div class="clear"></div>
				{/if}
			    {/block}
			</div>
		    <div class="space">&nbsp;</div>

			{* Table head *}
			{block name='frontend_checkout_cart_cart_head'}
				{include file="frontend/checkout/cart_header.tpl"}
			{/block}

			{* Article items *}
			{foreach name=basket from=$sBasket.content item=sBasketItem key=key}
                {block name='frontend_checkout_cart_item'}
				{include file='frontend/checkout/cart_item.tpl'}
                {/block}
			{/foreach}

			{* Premium articles *}
			{block name='frontend_checkout_cart_premiums'}
                <div class="table_row noborder">
                    {include file='frontend/checkout/cart_footer_left.tpl'}
                </div>

                {* The tag is still open due to a template issue in the frontend/checkout/shipping_costs which has a unclosed div-tag *}
                <div class="table_row non">
                	<div class="table_row shipping">
                	{if $sBasket.content && !$sUserLoggedIn}
                		{if !$sUserData.additional.user.id}
                			{include file="frontend/checkout/shipping_costs.tpl"}
                		{/if}
                	{/if}
                </div>
			{/block}

			{* Table foot *}
			{block name='frontend_checkout_cart_cart_footer'}

			{include file="frontend/checkout/cart_footer.tpl"}

			</div>

			<div class="space">&nbsp;</div>
			{* Action Buttons *}
			{include file="frontend/checkout/actions.tpl"}
			<div class="space">&nbsp;</div>


			<div class="clear"></div>
			<div class="doublespace"></div>

			{if $sPremiums}
			<div class="table_head">
				<div class="grid_19">{s name="sCartPremiumsHeadline" namespace="frontend/checkout/premiums"}Bitte w&auml;hlen Sie zwischen den folgenden Pr&auml;mien{/s}</div>
			</div>
			{/if}
			{* Premium articles *}
			{include file='frontend/checkout/premiums.tpl'}
			{/block}
		</div>
	{/if}
</div>
{/block}
