
{* Step box *}
<div class="step_box">
	{block name='frontend_register_steps'}
	<ul>
		<li id="first_step" {if $sStepActive=='basket'}class="active"{/if}>
			{se class="icon" name="CheckoutStepBasketNumber"}{/se}
			{se class="text" name="CheckoutStepBasketText"}{/se}
		</li>
		<li {if $sStepActive=='register'}class="active"{/if}>
			{se class="icon" name="CheckoutStepRegisterNumber"}{/se}
			{se class="text" name="CheckoutStepRegisterText"}{/se}
		</li>
		<li id="last_step" {if $sStepActive=='finished'}class="active"{elseif !$sUserLoggedIn}class="grey"{/if}>
			{se class="icon" name="CheckoutStepConfirmNumber"}{/se}
			{se class="text" name="CheckoutStepConfirmText"}{/se}
		</li>
	</ul>
	{/block}
</div>
<div class="clear">&nbsp;</div>
