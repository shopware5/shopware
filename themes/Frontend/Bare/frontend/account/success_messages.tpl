{if $sSuccessAction}
	{$successText=''}
	{if $sSuccessAction == 'billing'}
		{$successText="{s name='AccountBillingSuccess'}{/s}"}
	{elseif $sSuccessAction == 'shipping'}
		{$successText="{s name='AccountShippingSuccess'}{/s}"}
	{elseif $sSuccessAction == 'payment'}
		{$successText="{s name='AccountPaymentSuccess'}{/s}"}
	{elseif $sSuccessAction == 'account'}
		{$successText="{s name='AccountAccountSuccess'}{/s}"}
	{elseif $sSuccessAction == 'newsletter'}
		{$successText="{s name='AccountNewsletterSuccess'}{/s}"}
	{elseif $sSuccessAction == 'optinnewsletter'}
		{$successText="{s name='sMailConfirmation' namespace='frontend'}{/s}"}
	{elseif $sSuccessAction == 'deletenewsletter'}
		{$successText="{s name='NewsletterMailDeleted' namespace='frontend/account/internalMessages'}{/s}"}
	{elseif $sSuccessAction == 'resetPassword'}
		{$successText="{s name='PasswordResetNewSuccess' namespace='frontend/account/reset_password'}{/s}"}
	{/if}

	<div class="account--success">
		{include file="frontend/_includes/messages.tpl" type="success" content=$successText}
	</div>
{/if}