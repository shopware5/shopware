{extends file='frontend/index/index.tpl'}

{* Title *}
{block name='frontend_index_header_title'}
	{s name="CartTitle"}{/s} | {config name=shopName}
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}<div class="space">&nbsp;</div>{/block}

{* Step Box *}
{block name="frontend_index_content_top"}
	<div id="stepbox">
	{block name="frontend_basket_step_box"}
		{include file="frontend/register/steps.tpl" sStepActive="basket"}
	{/block}
	</div>

	{* Empty basket *}
	{if !$sBasket.content}
	{block name='frontend_basket_basket_is_empty'}
		<div class="space">&nbsp;</div>
		<div class="notice bold center">{se name="CartInfoEmpty"}{/se}</div>
	{/block}

	{* Cross-Selling *}
	{block name='frontend_checkout_crossselling'}
		<div class="listing" id="listing">
			{foreach from=$sCrossSelling item=sArticle key=key name="counter"}
				{include file="frontend/listing/box_article.tpl"}
			{/foreach}
		</div>
	{/block}
	{/if}

{/block}

{* Hide sidebar left *}
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
					<a href="{if {config name=always_select_payment}}{url controller='checkout' action='shippingPayment'}{else}{url controller='checkout' action='confirm'}{/if}" title="{s name='CheckoutActionsLinkProceed' namespace="frontend/checkout/actions"}{/s}" class="button-right large right checkout" >
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
					{if $sBasket.content && !$sUserLoggedIn && {config name=basketShowCalculation}}
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
{block name='frontend_index_content_right'}{/block}
