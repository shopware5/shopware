{extends file='frontend/index/minimal.tpl'}

{* Title *}
{block name='frontend_index_header_title'}
	{s name="CartTitle"}{/s} | {config name=shopName}
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}<div class="space">&nbsp;</div>{/block}

{block name='frontend_index_navigation_categories_top'}
    {include file="frontend/register/steps.tpl" sStepActive="basket"}
{/block}

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

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}


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


		{block name='frontend_checkout_cart_deliveryfree'}{/block}

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

            <div class="panel has--border">
                <div class="panel--body">
                    {* Product table header *}
                    {block name='frontend_checkout_cart_cart_head'}
                        {include file="frontend/checkout/cart_header.tpl"}
                    {/block}
                </div>
            </div>
        </div>



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
{block name='frontend_index_content_right'}{/block}
