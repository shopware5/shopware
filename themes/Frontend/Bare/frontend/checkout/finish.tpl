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

		{block name='frontend_checkout_finish_info'}
			<div class="finish--info">

				{block name='frontend_checkout_finish_billing_address'}
					<div class="finish--billing block panel has--border is--rounded">

						{block name='frontend_checkout_finish_billing_address_title'}
							<h2 class="panel--title is--underline">{s name="ConfirmHeaderBilling" namespace="frontend/checkout/confirm_left"}{/s}</h2>
						{/block}

						{block name='frontend_checkout_finish_billing_address_content'}
							<div class="panel--body is--wide">
								{if $sUserData.billingaddress.company}
									<strong>{$sUserData.billingaddress.company}{if $sUserData.billingaddress.department}<br />{$sUserData.billingaddress.department}{/if}</strong>
									<br>
								{/if}

								{if $sUserData.billingaddress.salutation eq "mr"}
									{s name="ConfirmSalutationMr" namespace="frontend/checkout/confirm_left"}{/s}
								{else}
									{s name="ConfirmSalutationMs" namespace="frontend/checkout/confirm_left"}{/s}
								{/if}

								{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
								{$sUserData.billingaddress.street}<br />
								{if $sUserData.billingaddress.additional_address_line1}{$sUserData.billingaddress.additional_address_line1}<br />{/if}
								{if $sUserData.billingaddress.additional_address_line2}{$sUserData.billingaddress.additional_address_line2}<br />{/if}
                                {if {config name=showZipBeforeCity}}{$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}{else}{$sUserData.billingaddress.city} {$sUserData.billingaddress.zipcode}{/if}<br />
								{if $sUserData.additional.state.statename}{$sUserData.additional.state.statename}<br />{/if}
								{$sUserData.additional.country.countryname}
							</div>
						{/block}
					</div>
				{/block}

				{block name='frontend_checkout_finish_shipping_address'}
					<div class="finish--shipping block panel has--border is--rounded">

						{block name='frontend_checkout_finish_shipping_address_title'}
							<h2 class="panel--title is--underline">{s name="ConfirmHeaderShipping" namespace="frontend/checkout/confirm_left"}{/s}</h2>
						{/block}

						{block name='frontend_checkout_finish_shipping_address_content'}
							<div class="panel--body is--wide">
								{if $sUserData.shippingaddress.company}
									<strong>{$sUserData.shippingaddress.company}{if $sUserData.shippingaddress.department}<br />{$sUserData.shippingaddress.department}{/if}</strong>
									<br>
								{/if}

								{if $sUserData.shippingaddress.salutation eq "mr"}
									{s name="ConfirmSalutationMr" namespace="frontend/checkout/confirm_left"}{/s}
								{else}
									{s name="ConfirmSalutationMs" namespace="frontend/checkout/confirm_left"}{/s}
								{/if}

								{$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br/>
								{$sUserData.shippingaddress.street}<br />
								{if $sUserData.shippingaddress.additional_address_line1}{$sUserData.shippingaddress.additional_address_line1}<br />{/if}
								{if $sUserData.shippingaddress.additional_address_line2}{$sUserData.shippingaddress.additional_address_line2}<br />{/if}
                                {if {config name=showZipBeforeCity}}{$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}{else}{$sUserData.shippingaddress.city} {$sUserData.shippingaddress.zipcode}{/if}<br />
								{if $sUserData.additional.stateShipping.statename}{$sUserData.additional.stateShipping.statename}<br />{/if}
								{$sUserData.additional.countryShipping.countryname}
							</div>
						{/block}
					</div>
				{/block}

				{block name='frontend_checkout_finish_details'}
					<div class="finish--details block panel has--border is--rounded">

						{* @deprecated block *}
						{block name='frontend_checkout_finish_header_items'}
							{block name='frontend_checkout_finish_details_title'}
								<h2 class="panel--title is--underline">{s name="FinishHeaderInformation"}{/s}</h2>
							{/block}
						{/block}

						{block name='frontend_checkout_finish_details_content'}
							<div class="panel--body is--wide">

								{* Invoice number *}
								{block name='frontend_checkout_finish_invoice_number'}
									{if $sOrderNumber}
										<span class="is--bold">{s name="FinishInfoId"}{/s}</span> {$sOrderNumber}<br />
									{/if}
								{/block}

								{* Transaction number *}
								{block name='frontend_checkout_finish_transaction_number'}
									{if $sTransactionumber}
										<span class="is--bold">{s name="FinishInfoTransaction"}{/s}</span> {$sTransactionumber}<br />
									{/if}
								{/block}

								{* Payment method *}
								{block name='frontend_checkout_finish_payment_method'}
									{if $sPayment.description}
										<span class="is--bold">{s name="ConfirmHeaderPayment" namespace="frontend/checkout/confirm_left"}{/s}:</span> {$sPayment.description}<br />
									{/if}
								{/block}

								{* Dispatch method *}
								{block name='frontend_checkout_finish_dispatch_method'}
									{if $sDispatch.name}
										<span class="is--bold">{s name="CheckoutDispatchHeadline" namespace="frontend/checkout/confirm_dispatch"}{/s}:</span> {$sDispatch.name}
									{/if}
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
