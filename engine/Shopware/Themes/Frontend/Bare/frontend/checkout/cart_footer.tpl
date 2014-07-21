{* Add product using the sku *}
{block name='frontend_checkout_cart_footer_add_product'}
	<form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}" class="table--add-product add-product--form block-group">

		{block name='frontend_checkout_cart_footer_add_product_field'}
			<input name="sAdd" class="add-product--field block" type="text" placeholder="{s name='CheckoutFooterAddProductPlaceholder' namespace='frontend/checkout/cart_footer_left'}{/s}" />
		{/block}

		{block name='frontend_checkout_cart_footer_add_product_button'}
			<button type="submit" class="add-product--button btn btn--primary is--small block">
				<i class="icon--arrow-right"></i>
			</button>
		{/block}
	</form>
{/block}

{block name='frontend_checkout_cart_footer_element'}
    <div class="basket--footer">
        <div class="table--aggregation">
            {* Add product using a voucher *}
            {block name='frontend_checkout_cart_footer_add_voucher'}
                <form method="post" action="{url action='addVoucher' sTargetAction=$sTargetAction}" class="table--add-voucher add-voucher--form">

                    {block name='frontend_checkout_cart_footer_add_voucher_trigger'}
                        <input type="checkbox" id="add-voucher--trigger" class="add-voucher--checkbox">
                    {/block}

                    {block name='frontend_checkout_cart_footer_add_voucher_label'}
                        <label for="add-voucher--trigger" class="add-voucher--label">{s name="CheckoutFooterVoucherTrigger"}Ich habe einen Gutschein{/s}</label>
                    {/block}

                    <div class="add-voucher--panel is--hidden block-group">
                        {block name='frontend_checkout_cart_footer_add_voucher_field'}
                            <input type="text" class="add-voucher--field block" name="sVoucher" placeholder="{s name='CheckoutFooterAddVoucherLabelInline'}{/s}" />
                        {/block}

                        {block name='frontend_checkout_cart_footer_add_voucher_button'}
                            <button type="submit" class="add-voucher--button btn btn--primary is--small block">
                                <i class="icon--arrow-right"></i>
                            </button>
                        {/block}
                    </div>
                </form>
            {/block}

            {* Shipping costs pre-calculation *}
            {if $sBasket.content && !$sUserLoggedIn && !$sUserData.additional.user.id && {config name=basketShowCalculation}}

                {block name='frontend_checkout_shipping_costs_country_trigger'}
                    <a href="#show-hide--shipping-costs" class="table--shipping-costs-trigger">
                        <i class="icon--arrow-right"></i> {s name='CheckoutFooterEstimatedShippingCosts'}{/s}
                    </a>
                {/block}

                {block name='frontend_checkout_shipping_costs_country_include'}
                    {include file="frontend/checkout/shipping_costs.tpl"}
                {/block}
            {/if}
        </div>

        {block name='frontend_checkout_cart_footer_field_labels'}
            <ul class="aggregation--list">

                {* Basket sum *}
                {block name='frontend_checkout_cart_footer_field_labels_sum'}
                    <li class="list--entry block-group entry--sum">

                        {block name='frontend_checkout_cart_footer_field_labels_sum_label'}
                            <div class="entry--label block">
                                {s name="CartFooterSum"}{/s}
                            </div>
                        {/block}

                        {block name='frontend_checkout_cart_footer_field_labels_sum_value'}
                            <div class="entry--value block">
                                {$sBasket.Amount|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}
                            </div>
                        {/block}
                    </li>
                {/block}

                {* Shipping costs *}
                {block name='frontend_checkout_cart_footer_field_labels_shipping'}
                    <li class="list--entry block-group entry--shipping">

                        {block name='frontend_checkout_cart_footer_field_labels_shipping_label'}
                            <div class="entry--label block">
                                {s name="CartFooterShipping"}{/s}
                            </div>
                        {/block}

                        {block name='frontend_checkout_cart_footer_field_labels_shipping_value'}
                            <div class="entry--value block">
                                {$sShippingcosts|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}
                            </div>
                        {/block}
                    </li>
                {/block}

                {* Total sum *}
                {block name='frontend_checkout_cart_footer_field_labels_total'}
                    <li class="list--entry block-group entry--total">

                        {block name='frontend_checkout_cart_footer_field_labels_total_label'}
                            <div class="entry--label block">
                                {s name="CartFooterTotal"}{/s}
                            </div>
                        {/block}

                        {block name='frontend_checkout_cart_footer_field_labels_total_value'}
                            <div class="entry--value block is--no-star">
                                {if $sAmountWithTax && $sUserData.additional.charge_vat}{$sAmountWithTax|currency}{else}{$sAmount|currency}{/if}
                            </div>
                        {/block}
                    </li>
                {/block}

                {* Total net *}
                {block name='frontend_checkout_cart_footer_field_labels_totalnet'}
                    {if $sUserData.additional.charge_vat}
                        <li class="list--entry block-group entry--totalnet">

                            {block name='frontend_checkout_cart_footer_field_labels_totalnet_label'}
                                <div class="entry--label block">
                                    {s name="CartFooterTotalNet"}{/s}
                                </div>
                            {/block}

                            {block name='frontend_checkout_cart_footer_field_labels_totalnet_value'}
                                <div class="entry--value block is--no-star">
                                    {$sAmountNet|currency}
                                </div>
                            {/block}
                        </li>
                    {/if}
                {/block}

                {* Taxes *}
                {block name='frontend_checkout_cart_footer_field_labels_taxes'}
                    {if $sUserData.additional.charge_vat}
                        {foreach $sBasket.sTaxRates as $rate => $value}

                            {block name='frontend_checkout_cart_footer_field_labels_taxes_entry'}
                                <li class="list--entry block-group entry--taxes">

                                    {block name='frontend_checkout_cart_footer_field_labels_taxes_label'}
                                        <div class="entry--label block">
                                            {s name="CartFooterTotalTax"}{/s}
                                        </div>
                                    {/block}

                                    {block name='frontend_checkout_cart_footer_field_labels_taxes_value'}
                                        <div class="entry--value block is--no-star">
                                            {$value|currency}
                                        </div>
                                    {/block}
                                </li>
                            {/block}
                        {/foreach}
                    {/if}
                {/block}
            </ul>
        {/block}
    </div>
{/block}

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