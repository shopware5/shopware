{extends file='frontend/index/index.tpl'}

{* Title *}
{block name='frontend_index_header_title'}
	{s name="CartTitle"}{/s} | {config name=shopName}
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}{/block}

{* Step Box *}
{block name="frontend_index_content_top"}

	{* Empty basket *}
	{if !$sBasket.content}
        {block name='frontend_basket_basket_is_empty'}
			<div class="panel">
				<div class="panel--body">
					{include file="frontend/_includes/messages.tpl" type="warning" content="{s name='CartInfoEmpty'}{/s}"}
				</div>
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

        {* Product table *}
        <div class="product--table {if {config name=BasketShippingInfo}} has--dispatch-info{/if}">

			{* Deliveryfree dispatch notification *}
			{block name='frontend_checkout_cart_deliveryfree'}
				{if $sShippingcostsDifference}
					{$shippingDifferenceContent="<strong>{s name='CartInfoFreeShipping'}{/s}</strong> {s name='CartInfoFreeShippingDifference'}{/s}"}
					{include file="frontend/_includes/messages.tpl" type="warning" content="{$shippingDifferenceContent}"}
				{/if}
			{/block}

			{* Error messages *}
			{block name='frontend_checkout_cart_error_messages'}
				{include file="frontend/checkout/error_messages.tpl"}
			{/block}

            <div class="table--actions">

				<div class="main--actions">
                    {* Contiune shopping *}
                    {if $sBasket.sLastActiveArticle.link}
                        {block name="frontend_checkout_actions_link_last"}
                            <a href="{$sBasket.sLastActiveArticle.link}" title="{s name='CheckoutActionsLinkLast' namespace="frontend/checkout/actions"}{/s}" class="btn btn--secondary is--left">
                                {s name="CheckoutActionsLinkLast" namespace="frontend/checkout/actions"}{/s}
                            </a>
                        {/block}
                    {/if}

					{block name="frontend_checkout_actions_confirm"}

						{* Forward to the checkout *}
						{if !$sMinimumSurcharge && !$sDispatchNoOrder}
							{block name="frontend_checkout_actions_checkout"}
								<a href="{url action=confirm}" title="{s name='CheckoutActionsLinkProceedShort' namespace="frontend/checkout/actions"}{/s}" class="btn btn--primary right">
									{s name="CheckoutActionsLinkProceedShort" namespace="frontend/checkout/actions"}{/s} <i class="icon--arrow-right"></i>
								</a>
							{/block}
						{/if}
					{/block}
				</div>
            </div>

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
								<div class="panel--header secondary premium--headline">
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

			<div class="table--actions actions--bottom">
				{block name="frontend_checkout_actions_confirm_bottom"}

					<div class="main--actions">

                        {* Contiune shopping *}
                        {if $sBasket.sLastActiveArticle.link}
                            {block name="frontend_checkout_actions_link_last_bottom"}
                                <a href="{$sBasket.sLastActiveArticle.link}" title="{s name='CheckoutActionsLinkLast' namespace="frontend/checkout/actions"}{/s}" class="btn btn--secondary is--left">
                                    {s name="CheckoutActionsLinkLast" namespace="frontend/checkout/actions"}{/s}
                                </a>
                            {/block}
                        {/if}

						{* Forward to the checkout *}
						{if !$sMinimumSurcharge && !$sDispatchNoOrder}
							{block name="frontend_checkout_actions_confirm_bottom_checkout"}
								<a href="{url action=confirm}" title="{s name='CheckoutActionsLinkProceedShort' namespace="frontend/checkout/actions"}{/s}" class="btn btn--primary right">
									{s name="CheckoutActionsLinkProceedShort" namespace="frontend/checkout/actions"}{/s} <i class="icon--arrow-right"></i>
								</a>
							{/block}
						{/if}
					</div>

					{if !$sMinimumSurcharge && ($sInquiry || $sDispatchNoOrder)}
						{block name="frontend_checkout_actions_inquiry"}
							<a href="{$sInquiryLink}" title="{s name='CheckoutActionsLinkOffer' namespace="frontend/checkout/actions"}{/s}" class="btn btn--secondary btn--inquiry">
								{s name="CheckoutActionsLinkOffer" namespace="frontend/checkout/actions"}{/s}
							</a>
						{/block}
					{/if}
				{/block}
			</div>

            {block name="frontend_checkout_footer"}
            <footer class="table--footer block-group">

                {* Benefits *}
                {block name="frontend_checkout_footer_benefits"}
                    <div class="footer--benefit block">
                        {block name="frontend_checkout_footer_headline_benefit"}
                            <h4 class="benefit--headline">{s name="CheckoutFooterBenefitHeadlineForYou"}Unserer Vorteil für Sie{/s}</h4>
                        {/block}

                        {block name="frontend_checkout_footer_benefits_list"}
                            <ul class="list--unstyled benefit--list">

                                {block name="frontend_checkout_footer_benefits_list_entry_1"}
                                    <li class="list--entry">
                                        <i class="icon--check"></i>
                                        {s name='RegisterInfoAdvantagesEntry1' namespace="frontend/register/index"}{/s}
                                    </li>
                                {/block}

                                {block name="frontend_checkout_footer_benefits_list_entry_2"}
                                    <li class="list--entry">
                                        <i class="icon--check"></i>
                                        {s name='RegisterInfoAdvantagesEntry2' namespace="frontend/register/index"}{/s}
                                    </li>
                                {/block}

                                {block name="frontend_checkout_footer_benefits_list_entry_3"}
                                    <li class="list--entry">
                                        <i class="icon--check"></i>
                                        {s name='RegisterInfoAdvantagesEntry3' namespace="frontend/register/index"}{/s}
                                    </li>
                                {/block}

                                {block name="frontend_checkout_footer_benefits_list_entry_4"}
                                    <li class="list--entry">
                                        <i class="icon--check"></i>
                                        {s name='RegisterInfoAdvantagesEntry4' namespace="frontend/register/index"}{/s}
                                    </li>
                                {/block}
                            </ul>
                        {/block}
                    </div>
                {/block}

                {* Supported dispatch services *}
                {block name="frontend_checkout_footer_dispatch"}
                    <div class="footer--benefit block">
                        {block name="frontend_checkout_footer_headline_dispatch"}
                            <h4 class="benefit--headline">{s name="CheckoutFooterBenefitHeadlineDispatch"}Wir verschicken mit{/s}</h4>
                        {/block}

                        {block name="frontend_checkout_footer_text_dispatch"}
                            <p class="benefit--text">
                                {s name="CheckoutFooterBenefitTextDispatch"}Ihre Versandanbieter-Logos{/s}
                            </p>
                        {/block}
                    </div>
                {/block}

                {* Supported payment services *}
                {block name="frontend_checkout_footer_payment"}
                    <div class="footer--benefit block">
                        {block name="frontend_checkout_footer_headline_payment"}
                            <h4 class="benefit--headline">{s name="CheckoutFooterBenefitHeadlinePayment"}Zahlungsmethoden{/s}</h4>
                        {/block}

                        {block name="frontend_checkout_footer_text_payment"}
                            <p class="benefit--text">
                                {s name="CheckoutFooterBenefitTextPayment"}Ihre Zahlungsanbieter-Logos{/s}
                            </p>
                        {/block}
                    </div>
                {/block}
            </footer>
            {/block}
        </div>
	{/if}
</div>
{/block}
