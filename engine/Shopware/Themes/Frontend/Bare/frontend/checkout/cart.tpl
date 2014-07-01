{extends file='frontend/index/index.tpl'}

{* Title *}
{block name='frontend_index_header_title'}
	{s name="CartTitle"}{/s} | {config name=shopName}
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}{/block}

{block name='frontend_index_navigation_categories_top'}{/block}

{* Step Box *}
{block name="frontend_index_content_top"}

	{* Empty basket *}
	{if !$sBasket.content}
        {block name='frontend_basket_basket_is_empty'}
            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name='CartInfoEmpty'}{/s}"}
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

{* Main content *}
{block name='frontend_index_content'}
<div class="content block content--basket content--checkout">

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

        {* Product table *}
        <div class="product--table {if {config name=BasketShippingInfo}} has--dispatch-info{/if}">
            <div class="table--actions">
                {block name="frontend_checkout_actions_confirm"}
                    {if !$sMinimumSurcharge && !$sDispatchNoOrder}
                        <a href="{url action=confirm}" title="{s name='CheckoutActionsLinkProceed' namespace="frontend/checkout/actions"}{/s}" class="btn btn--primary is--right">
                            {s name="CheckoutActionsLinkProceed" namespace="frontend/checkout/actions"}{/s} <i class="icon--arrow-right"></i>
                        </a>
                    {/if}
                {/block}
            </div>

			{* Deliveryfree dispatch notification *}
			{block name='frontend_checkout_cart_deliveryfree'}
				{if $sShippingcostsDifference}
					{$shippingDifferenceContent="<strong>{s name='CartInfoFreeShipping'}{/s}</strong> {s name='CartInfoFreeShippingDifference'}{/s}"}
					{include file="frontend/_includes/messages.tpl" type="warning" content="{$shippingDifferenceContent}"}
				{/if}
			{/block}

			{* Product table content *}
            <div class="panel has--border">
                <div class="panel--body">

                    {* Product table header *}
                    {block name='frontend_checkout_cart_cart_head'}
                        {include file="frontend/checkout/cart_header.tpl"}
                    {/block}

                    {* Basket items *}
                    {foreach $sBasket.content as $sBasketItem}
                        {block name='frontend_checkout_cart_item'}
                            {include file='frontend/checkout/cart_item.tpl'}
                        {/block}
                    {/foreach}

					{* Product table footer *}
					{block name='frontend_checkout_cart_cart_footer'}
						{include file="frontend/checkout/cart_footer.tpl"}
					{/block}

					{* Premium products *}
					{block name='frontend_checkout_cart_premium'}
						{if $sPremiums}

							{* Headline *}
							{block name='frontend_checkout_cart_premium_headline'}
								<div class="panel--header secondary">
									{s name="CartPremiumsHeadline"}{/s}
								</div>
							{/block}

							{* Actual listing *}
							{block name='frontend_checkout_cart_premium_products'}
								{include file='frontend/checkout/premiums.tpl'}
							{/block}
						{/if}
					{/block}
                </div>
            </div>
        </div>
	{/if}
</div>
{/block}
