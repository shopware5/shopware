{extends file="frontend/index/index.tpl"}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}{/block}

{* Hide shop navigation *}
{block name='frontend_index_shop_navigation'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Step box *}
{block name='frontend_index_navigation_categories_top'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide top bar *}
{block name='frontend_index_top_bar_container'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Footer *}
{block name='frontend_index_footer'}
    {if !$theme.checkoutFooter}
        {$smarty.block.parent}
    {else}
        {block name='frontend_index_checkout_finish_footer'}
            {include file="frontend/index/footer_minimal.tpl"}
        {/block}
    {/if}
{/block}

{* Back to the shop button *}
{block name='frontend_index_logo_trusted_shops' append}
    {if $theme.checkoutHeader}
        <a href="{url controller='index'}"
           class="btn is--small btn--back-top-shop is--icon-left"
           title="{"{s name='FinishButtonBackToShop'}{/s}"|escape}">
            <i class="icon--arrow-left"></i>
            {s name="FinishButtonBackToShop"}{/s}
        </a>
    {/if}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="content checkout--content finish--content">

		{* Finish teaser message *}
		{block name='frontend_checkout_finish_teaser'}
			<div class="finish--teaser panel has--border is--rounded">

				{block name='frontend_checkout_finish_teaser_title'}
					<h2 class="panel--title teaser--title is--align-center">{s name="FinishHeaderThankYou"}{/s} {$sShopname|escapeHtml}!</h2>
				{/block}

				{block name='frontend_checkout_finish_teaser_content'}
					<div class="panel--body is--wide is--align-center">
                        {if $confirmMailDeliveryFailed}
                            {include file="frontend/_includes/messages.tpl" type="error" content="{s name="FinishInfoConfirmationMailFailed"}{/s}"}
                        {/if}

						<p class="teaser--text">
                            {if !$confirmMailDeliveryFailed}
                                {s name="FinishInfoConfirmationMail"}{/s}
                                <br />
                            {/if}

                            {s name="FinishInfoPrintOrder"}{/s}
                        </p>

						{block name='frontend_checkout_finish_teaser_actions'}
							<p class="teaser--actions">

                                {strip}
								{* Back to the shop button *}
								<a href="{url controller='index'}" class="btn is--secondary teaser--btn-back is--icon-left" title="{"{s name='FinishButtonBackToShop'}{/s}"|escape}">
									<i class="icon--arrow-left"></i>&nbsp;{"{s name="FinishButtonBackToShop"}{/s}"|replace:' ':'&nbsp;'}
								</a>

								{* Print button *}
								<a href="#" class="btn is--primary teaser--btn-print" onclick="self.print()" title="{"{s name='FinishLinkPrint'}{/s}"|escape}">
									{s name="FinishLinkPrint"}{/s}
								</a>
                                {/strip}
							</p>

                            {* Print notice *}
                            {block name='frontend_checkout_finish_teaser_print_notice'}
                                <p class="print--notice">
                                    {s name="FinishPrintNotice"}{/s}
                                </p>
                            {/block}
						{/block}
					</div>
				{/block}
			</div>
		{/block}

		{block name='frontend_checkout_finish_information_wrapper'}
			<div class="panel--group block-group information--panel-wrapper finish--info" data-panel-auto-resizer="true">

				{block name='frontend_checkout_finish_information_addresses'}

					{if $sAddresses.equal}

						{* Equal Billing & Shipping *}
						{block name='frontend_checkout_finish_information_addresses_equal'}
							<div class="information--panel-item information--panel-address">

								{block name='frontend_checkout_finish_information_addresses_equal_panel'}
									<div class="panel has--border is--rounded block information--panel finish--billing">

										{block name='frontend_checkout_finish_information_addresses_equal_panel_title'}
											<div class="panel--title is--underline">
												{s name='ConfirmAddressEqualTitle' namespace="frontend/checkout/confirm"}{/s}
											</div>
										{/block}

										{block name='frontend_checkout_finish_information_addresses_equal_panel_body'}
											<div class="panel--body is--wide">

												{block name='frontend_checkout_finish_information_addresses_equal_panel_billing'}
													<div class="billing--panel">
														{if $sAddresses.billing.company}
															<strong>{$sAddresses.billing.company}{if $sAddresses.billing.department}<br />{$sAddresses.billing.department}{/if}</strong>
															<br />
														{/if}

														{if $sAddresses.billing.salutation eq "mr"}
															{s name="ConfirmSalutationMr" namespace="frontend/checkout/confirm"}{/s}
														{else}
															{s name="ConfirmSalutationMs" namespace="frontend/checkout/confirm"}{/s}
														{/if}

														{$sAddresses.billing.firstname} {$sAddresses.billing.lastname}<br />
														{$sAddresses.billing.street}<br />
														{if $sAddresses.billing.additional_address_line1}{$sAddresses.billing.additional_address_line1}<br />{/if}
														{if $sAddresses.billing.additional_address_line2}{$sAddresses.billing.additional_address_line2}<br />{/if}
														{if {config name=showZipBeforeCity}}{$sAddresses.billing.zipcode} {$sAddresses.billing.city}{else}{$sAddresses.billing.city} {$sAddresses.billing.zipcode}{/if}<br />
														{if $sAddresses.billing.state.name}{$sAddresses.billing.state.name}<br />{/if}
														{$sAddresses.billing.country.name}
													</div>
												{/block}
											</div>
										{/block}
									</div>
								{/block}
							</div>
						{/block}

					{else}

						{* Separate Billing & Shipping *}
						{block name='frontend_checkout_finish_information_addresses_billing'}
							<div class="information--panel-item information--panel-item-billing">
								{* Billing address *}
								{block name='frontend_checkout_finish_information_addresses_billing_panel'}
									<div class="panel has--border block information--panel billing--panel finish--billing">

										{* Headline *}
										{block name='frontend_checkout_confirm_information_addresses_billing_panel_title'}
											<div class="panel--title is--underline">
												{s name="ConfirmHeaderBilling" namespace="frontend/checkout/confirm"}{/s}
											</div>
										{/block}

										{* Content *}
										{block name='frontend_checkout_finish_information_addresses_billing_panel_body'}
											<div class="panel--body is--wide">
												{if $sAddresses.billing.company}
													<strong>{$sAddresses.billing.company}{if $sAddresses.billing.department}<br />{$sAddresses.billing.department}{/if}</strong>
													<br />
												{/if}

												{if $sAddresses.billing.salutation eq "mr"}
													{s name="ConfirmSalutationMr" namespace="frontend/checkout/confirm"}{/s}
												{else}
													{s name="ConfirmSalutationMs" namespace="frontend/checkout/confirm"}{/s}
												{/if}

												{$sAddresses.billing.firstname} {$sAddresses.billing.lastname}<br />
												{$sAddresses.billing.street}<br />
												{if $sAddresses.billing.additional_address_line1}{$sAddresses.billing.additional_address_line1}<br />{/if}
												{if $sAddresses.billing.additional_address_line2}{$sAddresses.billing.additional_address_line2}<br />{/if}
												{if {config name=showZipBeforeCity}}{$sAddresses.billing.zipcode} {$sAddresses.billing.city}{else}{$sAddresses.billing.city} {$sAddresses.billing.zipcode}{/if}<br />
												{if $sAddresses.billing.state.name}{$sAddresses.billing.state.name}<br />{/if}
												{$sAddresses.billing.country.name}
											</div>
										{/block}
									</div>
								{/block}
							</div>
						{/block}

						{block name='frontend_checkout_finish_information_addresses_shipping'}
							<div class="information--panel-item information--panel-item-shipping">
								{block name='frontend_checkout_finish_information_addresses_shipping_panel'}
									<div class="panel has--border block information--panel shipping--panel finish--shipping">

										{* Headline *}
										{block name='frontend_checkout_finish_information_addresses_shipping_panel_title'}
											<div class="panel--title is--underline">
												{s name="ConfirmHeaderShipping" namespace="frontend/checkout/confirm"}{/s}
											</div>
										{/block}

										{* Content *}
										{block name='frontend_checkout_finish_information_addresses_shipping_panel_body'}
											<div class="panel--body is--wide">
												{if $sAddresses.shipping.company}
													<strong>{$sAddresses.shipping.company}{if $sAddresses.shipping.department}<br />{$sAddresses.shipping.department}{/if}</strong>
													<br />
												{/if}

												{if $sAddresses.shipping.salutation eq "mr"}
													{s name="ConfirmSalutationMr" namespace="frontend/checkout/confirm"}{/s}
												{else}
													{s name="ConfirmSalutationMs" namespace="frontend/checkout/confirm"}{/s}
												{/if}

												{$sAddresses.shipping.firstname} {$sAddresses.shipping.lastname}<br />
												{$sAddresses.shipping.street}<br />
												{if $sAddresses.shipping.additional_address_line1}{$sAddresses.shipping.additional_address_line1}<br />{/if}
												{if $sAddresses.shipping.additional_address_line2}{$sAddresses.shipping.additional_address_line2}<br />{/if}
												{if {config name=showZipBeforeCity}}{$sAddresses.shipping.zipcode} {$sAddresses.shipping.city}{else}{$sAddresses.shipping.city} {$sAddresses.shipping.zipcode}{/if}<br />
												{if $sAddresses.shipping.state.name}{$sAddresses.shipping.state.name}<br />{/if}
												{$sAddresses.shipping.country.name}
											</div>
										{/block}
									</div>
								{/block}
							</div>
						{/block}
					{/if}
				{/block}

				{* Payment method *}
				{block name='frontend_checkout_finish_information_payment'}
					<div class="information--panel-item">
						{block name='frontend_checkout_finish_payment_method_panel'}
							<div class="panel has--border block information--panel payment--panel finish--details">

								{block name='frontend_checkout_finish_left_payment_method_headline'}
									<div class="panel--title is--underline payment--title">
										{s name="FinishHeaderInformation"}{/s}
									</div>
								{/block}

								{block name='frontend_checkout_finish_left_payment_content'}
									<div class="panel--body is--wide payment--content">

										{* Invoice number *}
										{block name='frontend_checkout_finish_invoice_number'}
											{if $sOrderNumber}
												<strong>{s name="FinishInfoId"}{/s}</strong> {$sOrderNumber}<br />
											{/if}
										{/block}

										{* Transaction number *}
										{block name='frontend_checkout_finish_transaction_number'}
											{if $sTransactionumber}
												<strong>{s name="FinishInfoTransaction"}{/s}</strong> {$sTransactionumber}<br />
											{/if}
										{/block}

										{* Payment method *}
										{block name='frontend_checkout_finish_payment_method'}
											{if $sPayment.description}
												<strong>{s name="ConfirmHeaderPayment" namespace="frontend/checkout/confirm"}{/s}:</strong> {$sPayment.description}<br />
											{/if}
										{/block}

										{* Dispatch method *}
										{block name='frontend_checkout_finish_dispatch_method'}
											{if $sDispatch.name}
												<strong>{s name="CheckoutDispatchHeadline" namespace="frontend/checkout/confirm_dispatch"}{/s}:</strong> {$sDispatch.name}
											{/if}
										{/block}

									</div>
								{/block}

							</div>
						{/block}
					</div>
				{/block}
			</div>
		{/block}

		{block name='frontend_checkout_finish_items'}
			<div class="finish--table product--table">
				<div class="panel has--border">
					<div class="panel--body is--rounded">

						{* Table header *}
						{block name='frontend_checkout_finish_table_header'}
							{include file="frontend/checkout/finish_header.tpl"}
						{/block}

						{* Article items *}
						{foreach $sBasket.content as $key => $sBasketItem}
							{block name='frontend_checkout_finish_item'}
								{include file='frontend/checkout/finish_item.tpl' isLast=$sBasketItem@last}
							{/block}
						{/foreach}

						{* Table footer *}
						{block name='frontend_checkout_finish_table_footer'}
							{include file="frontend/checkout/finish_footer.tpl"}
						{/block}
					</div>
				</div>
			</div>
		{/block}
	</div>
{/block}
