
<div id="left" class="grid_4 first info">

	{block name='frontend_checkout_confirm_left_payment_method'}
	{if !$sRegisterFinished}
	<h2 class="headingbox largesize">{s name="ConfirmHeaderPayment"}{/s}</h2>
	<div class="payment_method">
		<div class="inner_container">
			<p>
				<strong>{$sUserData.additional.payment.description}</strong><br />
	            
	            {if !$sUserData.additional.payment.esdactive}
	                {s name="ConfirmInfoInstantDownload"}{/s}
	            {/if}
	        </p>
	        <a href="{url controller=account action=payment sTarget=checkout}" class="button-middle small">
				{s name="ConfirmLinkChangePayment"}{/s}
			</a>
		</div>
	</div>
	{/if}
	{/block}
	
	{block name='frontend_checkout_confirm_left_billing_address'}
	<h2 class="headingbox largesize">{s name="ConfirmHeaderBilling"}{/s}</h2>
	<div class="billing_address">
		<div class="inner_container">
			{if $sUserData.billingaddress.company}
			<p>
				{$sUserData.billingaddress.company}{if $sUserData.billingaddress.department}<br/>{$sUserData.billingaddress.department}{/if}
			</p>
			{/if}
			<p>
				{if $sUserData.billingaddress.salutation eq "mr"}{s name="ConfirmSalutationMr"}{/s}{else}{s name="ConfirmSalutationMs"}{/s}{/if}
				{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
				{$sUserData.billingaddress.street}<br />
				{$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}<br />
                {if $sUserData.additional.state.shortcode}{$sUserData.additional.state.shortcode} - {/if} {$sUserData.additional.country.countryname}
			</p>
			<a href="{url controller=account action=billing sTarget=checkout}" class="button-middle small">
				{s name="ConfirmLinkChangeBilling"}{/s}
			</a>
			<a href="{url controller=account action=selectBilling sTarget=checkout}" class="button-middle small">
				{s name="ConfirmLinkSelectBilling"}{/s}
			</a>
		</div>
	</div>
	{/block}
	
	{block name='frontend_checkout_confirm_left_shipping_address'}
	<h2 class="headingbox largesize">{s name="ConfirmHeaderShipping"}{/s}</h2>
	<div class="shipping_address">
		<div class="inner_container">
			
			{if $sUserData.shippingaddress.company}
			<p>
	        	{$sUserData.shippingaddress.company}{if $sUserData.shippingaddress.department}<br/>{$sUserData.shippingaddress.department}{/if}
	       	</p>
        	{/if}
	        
	        <p>
	        {if $sUserData.shippingaddress.salutation eq "mr"}
	        	{s name="ConfirmSalutationMr"}{/s}
	        {else}
	       		{s name="ConfirmSalutationMs"}{/s}
	        {/if}
	    	{$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br />
			{$sUserData.shippingaddress.street}<br />
			{$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}<br />
            {if $sUserData.additional.stateShipping.shortcode}{$sUserData.additional.stateShipping.shortcode} - {/if}{$sUserData.additional.countryShipping.countryname}
			</p>
			
			<a href="{url controller=account action=shipping sTarget=checkout}" class="button-middle small">
				{s name="ConfirmLinkChangeShipping"}{/s}
			</a>
			
			<a href="{url controller=account action=selectShipping sTarget=checkout}" class="button-middle small">
				{s name="ConfirmLinkSelectShipping"}{/s}
			</a>
		</div>
	</div>
	{/block}

</div>
