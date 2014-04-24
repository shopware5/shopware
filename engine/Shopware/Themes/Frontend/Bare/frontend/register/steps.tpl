{* Step box *}
<div class="steps--content panel--body center container">
	{block name='frontend_register_steps'}
		<ul class="steps--list">

			{* First Step - Basket *}
			{block name='frontend_register_steps_basket'}
				<li class="steps--entry step--basket{if $sStepActive=='basket'} is--active{/if}">
					<span class="icon">{s name="CheckoutStepBasketNumber"}{/s}</span>
					<span class="text"><span class="text--inner">{s name="CheckoutStepBasketText"}{/s}</span></span>
				</li>
			{/block}

			{* Spacer *}
			{block name='frontend_register_steps_spacer1'}
				<li class="steps--entry steps--spacer">
					<i class="icon--arrow-right"></i>
				</li>
			{/block}

			{* Second Step - Registration *}
			{block name='frontend_register_steps_register'}
				<li class="steps--entry step--register{if $sStepActive=='register'} is--active{/if}">
					<span class="icon">{s name="CheckoutStepRegisterNumber"}{/s}</span>
					<span class="text"><span class="text--inner">{s name="CheckoutStepRegisterText"}{/s}</span></span>
				</li>
			{/block}

			{* Spacer *}
			{block name='frontend_register_steps_spacer2'}
				<li class="steps--entry steps--spacer">
					<i class="icon--arrow-right"></i>
				</li>
			{/block}

			{* Third Step - Confirmation *}
			{block name='frontend_register_steps_confirm'}
				<li class="steps--entry step--confirm{if $sStepActive=='finished'} is--active{/if}">
					<span class="icon">{s name="CheckoutStepConfirmNumber"}{/s}</span>
					<span class="text"><span class="text--inner">{s name="CheckoutStepConfirmText"}{/s}</span></span>
				</li>
			{/block}
		</ul>
	{/block}
</div>
