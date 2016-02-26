
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
			{s name='sMailConfirmation' namespace='frontend'}{/s}
		{elseif $sSuccessAction == 'deletenewsletter'}
			{s name='NewsletterMailDeleted' namespace='frontend/account/internalMessages'}{/s}
		{elseif $sSuccessAction == 'resetPassword'}
			{s name='PasswordResetNewSuccess' namespace='frontend/account/reset_password'}{/s}
		{/if}
	</div>
{/if}
