{if $sSuccessAction}
	<div class="success bold center grid_16">
		{if $sSuccessAction == 'billing'}
			{se name='AccountBillingSuccess'}{/se}
		{elseif $sSuccessAction == 'shipping'}
			{se name='AccountShippingSuccess'}{/se}
		{elseif $sSuccessAction == 'payment'}
			{se name='AccountPaymentSuccess'}{/se}
		{elseif $sSuccessAction == 'account'}
			{se name='AccountAccountSuccess'}{/se}
		{elseif $sSuccessAction == 'newsletter'}
			{se name='AccountNewsletterSuccess'}{/se}
		{elseif $sSuccessAction == 'optinnewsletter'}
			{se namespace="frontend" name='sMailConfirmation'}{/se}
		{elseif $sSuccessAction == 'deletenewsletter'}
			{se namespace="frontend/account/internalMessages" name='NewsletterMailDeleted'}{/se}
		{/if}
	</div>
{/if}