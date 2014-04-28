{extends file="frontend/index/index.tpl"}

{* Javascript *}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
//<![CDATA[
	if(top!=self){
		top.location=self.location;
	}
//]]>
</script>
{/block}

{* Include the necessary stylesheets. We need inline styles here due to the fact that the colors are configuratable. *}
{block name="frontend_index_header_css_screen" append}
	<style type="text/css">
		#confirm .table, #confirm .country-notice {
			background: {config name=baskettablecolor};
		}
		#confirm .table .table_head {
			color: {config name=basketheaderfontcolor};
			background: {config name=basketheadercolor};
		}
	</style>
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}<hr class="clear" />{/block}

{block name="frontend_index_content_top"}
<div class="grid_20 first">

	{* Step box *}
	{include file="frontend/register/steps.tpl" sStepActive="finished"}

	{* AGB is not accepted by user *}
	{if $sAGBError}
		{include file="frontend/_includes/messages.tpl" type="error" content="{s name="ConfirmErrorAGB"}{/s}"}
	{/if}
</div>
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name="frontend_index_content"}
<div id="confirm" class="grid_16 push_2 first">

    {* Error messages *}
    {block name='frontend_checkout_confirm_error_messages'}
        {include file="frontend/checkout/error_messages.tpl"}
    {/block}

    <div class="outer-confirm-container">

        {* AGB and Revocation *}
        <div class="agb_cancelation grid_16 first">
            <h2 class="headingbox">
                {s name="ConfirmHeadlineAGBandRevocation"}AGB und Widerrufsbelehrung{/s}
            </h2>

            <div class="inner_container">

                {* Display the right of cancelation *}
                {if {config name=revocationnotice}}
                    <div class="confirm_accept modal_open">
                        {s name="ConfirmTextRightOfRevocationNew"}<p>Bitte beachten Sie bei Ihrer Bestellung auch unsere <a href="{url controller=custom sCustom=8 forceSecure}" data-modal-height="500" data-modal-width="800">Widerrufsbelehrung</a>.</p>{/s}
                    </div>
                {/if}

                {* AGB checkbox *}
                {block name='frontend_checkout_confirm_agb'}
                {/block}

                {* Newsletter registration *}
                {block name='frontend_checkout_confirm_newsletter'}
                    {if !$sUserData.additional.user.newsletter && {config name=newsletter}}
                        <div class="clear"></div>
                    {/if}
                {/block}

                {if {config name=additionalfreetext}}
                    <div class="agb_info">
                        {s name="ConfirmTextOrderDefault"}{/s}
                    </div>
                {/if}
            </div>
        </div>
        <div class="space"></div>

        {* Personal information *}
        <div class="personal-information grid_16 first">
            <h2 class="headingbox">
                {s name="ConfirmHeadlinePersonalInformation"}Ihre pers&ouml;nlichen Informationen{/s}
            </h2>

            <div class="inner_container">
                {if {config name=additionalfreetext}}
                    <p>
                        {s name="ConfirmInfoChange"}{/s}<br/>
                        {s name="ConfirmInfoPaymentData"}{/s}
                    </p>
                {/if}

                {* Billing address *}
                {block name='frontend_checkout_confirm_left_billing_address'}
                    <div class="invoice-address">
                        <h3 class="underline">{s name="ConfirmHeaderBilling" namespace="frontend/checkout/confirm_left"}{/s}</h3>

                        {if $sUserData.billingaddress.company}
                            <p>
                                {$sUserData.billingaddress.company}{if $sUserData.billingaddress.department}<br/>{$sUserData.billingaddress.department}{/if}
                            </p>
                        {/if}

                        <p>
                            {if $sUserData.billingaddress.salutation eq "mr"}
                                {s name="ConfirmSalutationMr" namespace="frontend/checkout/confirm_left"}Herr{/s}
                            {else}
                                {s name="ConfirmSalutationMs" namespace="frontend/checkout/confirm_left"}Frau{/s}
                            {/if}
                            {$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
                            {$sUserData.billingaddress.street} {$sUserData.billingaddress.streetnumber}<br />
                            {$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}<br />
                            {if $sUserData.additional.state.shortcode}{$sUserData.additional.state.shortcode} - {/if}{$sUserData.additional.country.countryname}


                        </p>

                        {* Action buttons *}
                        <div class="actions">
                            <a href="{url controller=account action=billing sTarget=checkout}" class="button-middle small">
                                {s name="ConfirmLinkChangeBilling" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>
                            <a href="{url controller=account action=selectBilling sTarget=checkout}" class="button-middle small">
                                {s name="ConfirmLinkSelectBilling" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>
                        </div>
                    </div>
                {/block}

                {* Shipping address *}
                {block name='frontend_checkout_confirm_left_shipping_address'}
                    <div class="shipping-address">
                        <h3 class="underline">{s name="ConfirmHeaderShipping" namespace="frontend/checkout/confirm_left"}{/s}</h3>
                        {if $sUserData.shippingaddress.company}
                            <p>
                                {$sUserData.shippingaddress.company}{if $sUserData.shippingaddress.department}<br/>{$sUserData.shippingaddress.department}{/if}
                            </p>
                        {/if}

                        <p>
                            {if $sUserData.shippingaddress.salutation eq "mr"}
                                {s name="ConfirmSalutationMr" namespace="frontend/checkout/confirm_left"}Herr{/s}
                            {else}
                                {s name="ConfirmSalutationMs" namespace="frontend/checkout/confirm_left"}Frau{/s}
                            {/if}
                            {$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br />
                            {$sUserData.shippingaddress.street} {$sUserData.shippingaddress.streetnumber}<br />
                            {$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}<br />
                            {if $sUserData.additional.stateShipping.shortcode}{$sUserData.additional.stateShipping.shortcode} - {/if}{$sUserData.additional.countryShipping.countryname}
                        </p>

                        {* Action buttons *}
                        <div class="actions">
                            <a href="{url controller=account action=shipping sTarget=checkout}" class="button-middle small">
                                {s name="ConfirmLinkChangeShipping" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>

                            <a href="{url controller=account action=selectShipping sTarget=checkout}" class="button-middle small">
                                {s name="ConfirmLinkSelectShipping" namespace="frontend/checkout/confirm_left"}{/s}
                            </a>
                        </div>
                    </div>
                {/block}

                {* Payment method *}
                {block name='frontend_checkout_confirm_left_payment_method'}
                    {if !$sRegisterFinished}
                        <div class="payment-display">
                            <h3 class="underline">{s name="ConfirmHeaderPayment" namespace="frontend/checkout/confirm_left"}{/s}</h3>
                            <p>
                                <strong>{$sUserData.additional.payment.description}</strong><br />

                                {if !$sUserData.additional.payment.esdactive}
                                    {s name="ConfirmInfoInstantDownload" namespace="frontend/checkout/confirm_left"}{/s}
                                {/if}
                            </p>

                            {* Action buttons *}
                            <div class="actions">
                                <a href="{url controller=account action=payment sTarget=checkout}" class="button-middle small">
                                    {s name="ConfirmLinkChangePayment" namespace="frontend/checkout/confirm_left"}{/s}
                                </a>
                            </div>
                        </div>
                    {/if}
                {/block}

                {* Clear floating and add a spacing *}
                <div class="clear"></div>
                <div class="space"></div>

                {* Dispatch selection *}
                {block name='frontend_checkout_confirm_shipping'}
                    {include file="frontend/checkout/confirm_dispatch.tpl"}
                {/block}

                {* Payment selection *}
                {block name='frontend_checkout_confirm_payment'}
                    {include file='frontend/checkout/confirm_payment.tpl'}
                {/block}
            </div>
        </div>

        <div class="space"></div>

        {if {config name=commentvoucherarticle}||{config name=premiumarticles}||{config name=bonussystem} && {config name=bonus_system_active} && {config name=displaySlider}}
            <div class="additional-options grid_16 first">

                <h2 class="headingbox">{s name="ConfirmHeadlineAdditionalOptions"}Weitere Optionen{/s}</h2>
                <div class="inner_container">

                    {* Voucher and add article *}
                    {if {config name=commentvoucherarticle}}
                        <div class="voucher-add-article">

                            {block name='frontend_checkout_table_footer_left_add_voucher'}
                                <div class="vouchers">
                                    <form method="post" action="{url action='addVoucher' sTargetAction=$sTargetAction}">
                                        {block name='frontend_checkout_table_footer_left_add_voucher_agb'}
                                        {if !{config name='IgnoreAGB'}}
                                            <input type="hidden" class="agb-checkbox" name="sAGB"
                                                   value="{if $sAGBChecked}1{else}0{/if}"/>
                                        {/if}
                                        {/block}
                                        <label for="basket_add_voucher">{s name="CheckoutFooterLabelAddVoucher" namespace="frontend/checkout/cart_footer_left"}{/s}</label>
                                        <input type="text" class="text" id="basket_add_voucher" name="sVoucher"
                                               onfocus="this.value='';"
                                               value="{s name='CheckoutFooterAddVoucherLabelInline' namespace="frontend/checkout/cart_footer_left"}{/s}"/>
                                        <input type="submit"
                                               value="{s name='CheckoutFooterActionAddVoucher' namespace="frontend/checkout/cart_footer_left"}{/s}"
                                               class="box_send"/>
                                    </form>
                                </div>
                            {/block}

                            {block name='frontend_checkout_table_footer_left_add_article'}
                                <div class="add_article">
                                    <form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}">
                                        <label for="basket_add_article">{s name='CheckoutFooterLabelAddArticle' namespace="frontend/checkout/cart_footer_left"}{/s}:</label>
                                        <input id="basket_add_article" name="sAdd" type="text" value="{s name='CheckoutFooterIdLabelInline' namespace="frontend/checkout/cart_footer_left"}{/s}" onfocus="this.value='';" class="ordernum text" />
                                        <input type="submit" class="box_send" value="{s name='CheckoutFooterActionAdd' namespace="frontend/checkout/cart_footer_left"}{/s}" />
                                    </form>
                                </div>
                            {/block}
                        </div>

                        {* Comment functionality *}
                        {block name='frontend_checkout_confirm_comment'}
                            <div class="user-comment">
                                <label for="sComment">{s name="ConfirmLabelComment" namespace="frontend/checkout/confirm"}{/s}</label>
                                <textarea name="sComment" rows="5" cols="20">{$sComment|escape}</textarea>
                            </div>
                            <div class="clear"></div>
                        {/block}
                        <div class="space"></div>
                    {/if}

					{* Premiums articles *}
					{block name='frontend_checkout_confirm_premiums'}
						{if $sPremiums}
							{if {config name=premiumarticles}}
								<h2 class="headingbox">{s name="sCartPremiumsHeadline" namespace="frontend/checkout/premiums"}{/s}</h2>
								{include file='frontend/checkout/premiums.tpl'}
							{/if}
						{/if}
					{/block}
                </div>
            </div>
            <div class="space"></div>
        {/if}

        <div class="table grid_16">
			{block name='frontend_checkout_confirm_confirm_head'}
            	{include file="frontend/checkout/confirm_header.tpl"}
			{/block}

			{block name='frontend_checkout_confirm_item_before'}{/block}

            {* Article items *}
			{block name='frontend_checkout_confirm_item_outer'}
            {foreach name=basket from=$sBasket.content item=sBasketItem key=key}
                {block name='frontend_checkout_confirm_item'}
                	{include file='frontend/checkout/confirm_item.tpl'}
                {/block}
            {/foreach}
			{/block}

			{block name='frontend_checkout_confirm_item_after'}{/block}

            {* Table footer *}
			{block name='frontend_checkout_confirm_confirm_footer'}
            	{include file="frontend/checkout/confirm_footer.tpl"}
			{/block}
        </div>

        <div class="space">&nbsp;</div>

        {* Additional footer *}
        <div class="additional_footer">
            <form method="post" action="{if $sPayment.embediframe || $sPayment.action}{url action='payment'}{else}{url action='finish'}{/if}">

                <div class="clear">&nbsp;</div>

                {if !$sUserData.additional.user.newsletter && {config name=newsletter}}
                    <input type="hidden" class="newsletter-checkbox" name="sNewsletter" value="{if $sNewsletter}1{else}0{/if}" />
                {/if}

                {if {config name=commentvoucherarticle}}
                    <input type="hidden" class="comment-textarea" name="sComment" value="{$sComment|escape}" />
                {/if}


                    {block name='frontend_checkout_confirm_footer'}

                        {if !$sLaststock.hideBasket}
                            {block name='frontend_checkout_confirm_submit'}
                            {* Submit order button *}
                            <div class="actions">
                                {if $sPayment.embediframe || $sPayment.action}
                                    <input type="submit" class="button-right large" id="basketButton" value="{s name='ConfirmDoPayment'}Zahlung durchführen{/s}" />
                                {else}
                                    <input type="submit" class="button-right large" id="basketButton" value="{s name='ConfirmActionSubmit'}{/s}" />
                                {/if}
                            </div>
                            {/block}
                        {else}
                            {block name='frontend_checkout_confirm_stockinfo'}
								{include file="frontend/_includes/messages.tpl" type="error" content="{s name="ConfirmErrorStock"}{/s}"}
                            {/block}
                        {/if}
                        <div class="clear">&nbsp;</div>
                    {/block}
                {block name='frontend_checkout_confirm_agb_checkbox'}
                <div class="agb_accept">
                    {if !{config name='IgnoreAGB'}}
                    	<input type="checkbox" class="left" name="sAGB" id="sAGB" {if $sAGBChecked} checked="checked"{/if} />
                    {/if}

					{* Additional hidden input for IE11 fix empty post body *}
					<input type="hidden" name="ieCheckValue" value="42" />
                    <label for="sAGB" class="chklabel modal_open {if $sAGBError}instyle_error{/if}">{s name="ConfirmTerms"}{/s}</label>
                </div>
                {/block}
                {if !$sUserData.additional.user.newsletter && {config name=newsletter}}
                    <div class="more_info">
                        <p>
                            <input type="checkbox" name="sNewsletter" value="1" class="chkbox"{if $sNewsletter} checked="checked"{/if} />
                            <label for="sNewsletter" class="chklabel">
                                {s name="ConfirmLabelNewsletter"}{/s}
                            </label>
                        </p>
                    </div>
                {/if}
            </form>
        </div>
	</div>
	<div class="doublespace">&nbsp;</div>
</div>
{/block}
