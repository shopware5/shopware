<div class="register--login content block">

	{* New customer *}
	{block name='frontend_register_login_newcustomer'}
		<div class="register--new-customer">
			<button type="button" class="btn btn--secondary">{s name="LoginLinkRegister2" namespace="frontend/account/login"}{/s}</button>
		</div>
	{/block}

	{* Existing customer *}
	{block name='frontend_register_login_customer'}
		<div class="register--existing-customer panel has--border">

			<h2 class="panel--title is--underline">{s name="LoginHeaderExistingCustomer" namespace="frontend/account/login"}{/s}</h2>
			<div class="panel--body is--wide">
				{block name='frontend_register_login_form'}
					<form name="sLogin" method="post" action="{url action=login}">
						{if $sTarget}<input name="sTarget" type="hidden" value="{$sTarget|escape}" />{/if}

						{block name='frontend_register_login_description'}
							<div class="register--login-description">{s name="LoginHeaderFields" namespace="frontend/account/login"}{/s}</div>
						{/block}

						{block name='frontend_register_login_input_email'}
							<div class="register--login-email">
								<input name="email" placeholder="{s name="LoginLabelMail" namespace="frontend/account/login"}{/s}" type="text" tabindex="1" value="{$sFormData.email|escape}" id="email" class="register--login-field{if $sErrorFlag.email} has--error{/if}" />
							</div>
						{/block}

						{block name='frontend_register_login_input_password'}
							<div class="register--login-password">
								<input name="password" placeholder="{s name="LoginLabelPassword" namespace="frontend/account/login"}{/s}" type="password" tabindex="2" id="passwort" class="register--login-field{if $sErrorFlag.password} has--error{/if}" />
							</div>
						{/block}

						{block name='frontend_register_login_input_lostpassword'}
							<div class="register--login-lostpassword">
								<a href="{url action=password}" title="{s name="LoginLinkLostPassword" namespace="frontend/account/login"}{/s}">
									{s name="LoginLinkLostPassword" namespace="frontend/account/login"}{/s}
								</a>
							</div>
						{/block}

						{block name='frontend_register_login_input_form_submit'}
							<div class="register--login-action">
								<button type="submit" class="btn btn--primary" name="Submit">{s name="LoginLinkLogon" namespace="frontend/account/login"}{/s} <i class="icon--arrow-right is--small"></i></button>
							</div>
						{/block}
					</form>
				{/block}
			</div>

		</div>
	{/block}
</div>