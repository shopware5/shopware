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

{* Sidebar left *}

{block name='frontend_index_content_left'}
{if $sBasket.content && !$sUserLoggedIn}
	{include file="frontend/checkout/cart_left.tpl"}
{/if}
{/block}


{* Main content *}
{block name='frontend_index_content'}
<div class="grid_16 {if $sUserLoggedIn}push_2{/if} last" id="basket">

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

		{block name='frontend_checkout_cart_deliveryfree'}
		{* Deliveryfree *}
		{if $sShippingcostsDifference}
			<div class="notice">
				<strong>{se name="CartInfoFreeShipping"}{/se}</strong>
				{se name="CartInfoFreeShippingDifference"}{/se}
			</div>
		{/if}
		{/block}

		<div class="table grid_16">
			{* Table head *}
			{block name='frontend_checkout_cart_cart_head'}
				{include file="frontend/checkout/cart_header.tpl"}
			{/block}

			{block name='frontend_checkout_cart_item_before'}{/block}

			{* Article items *}
			{block name='frontend_checkout_cart_item_outer'}
				{foreach name=basket from=$sBasket.content item=sBasketItem key=key}
					{block name='frontend_checkout_cart_item'}
						{include file='frontend/checkout/cart_item.tpl'}
					{/block}
				{/foreach}
			{/block}

			{block name='frontend_checkout_cart_item_after'}{/block}

			{* Premium articles *}
			{block name='frontend_checkout_cart_premiums'}
				{include file='frontend/checkout/premiums.tpl'}
			{/block}

			{* Table foot *}
			{block name='frontend_checkout_cart_cart_footer'}
				{include file="frontend/checkout/cart_footer.tpl"}
			{/block}
		</div>

		<div class="space">&nbsp;</div>

		{* Action Buttons *}
		{include file="frontend/checkout/actions.tpl"}

		<div class="space">&nbsp;</div>
	{/if}
</div>
{/block}
{block name='frontend_index_content_right'}{/block}
