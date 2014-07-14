{* Step box *}
<div class="steps--container container">
    <div class="steps--content panel--body center">
        {block name='frontend_register_steps'}
            <ul class="steps--list">

				{* First Step - Registration *}
				{block name='frontend_register_steps_register'}
					<li class="steps--entry step--register{if $sStepActive=='register'} is--active{/if}">
						<span class="icon">{s name="CheckoutStepLoginNumber"}{/s}</span>
						<span class="text"><span class="text--inner">{s name="CheckoutStepRegisterText"}{/s}</span></span>
					</li>
				{/block}

                {* Spacer *}
                {block name='frontend_register_steps_spacer1'}
                    <li class="steps--entry steps--spacer">
                        <i class="icon--arrow-right"></i>
                    </li>
                {/block}

                {* Second Step - Payment *}
                {block name='frontend_register_steps_payment'}
                    <li class="steps--entry step--payment{if $sStepActive=='payment'} is--active{/if}">
                        <span class="icon">{s name="CheckoutStepPaymentNumber"}{/s}</span>
                        <span class="text"><span class="text--inner">{s name="CheckoutStepPaymentText"}{/s}</span></span>
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
</div>