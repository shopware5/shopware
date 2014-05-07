{* New customer *}
<div class="register--new-customer">
	<button type="submit" class="btn btn--secondary">Ich bin Neukunde</button>
</div>

<div class="register--login panel content has--border">

	{* block name='frontend_account_login_new'}
	<div class="register--new-customer panel">
		<h2 class="panel--title is--underline">{s name="LoginHeaderNew"}{/s} {$sShopname}</h2>
		<div class="panel--body">
			<p>{s name="LoginInfoNew"}{/s}</p>
			<form method="post" name="new_customer" class="new_customer_form" action="{url controller='register'}">
				{if !{config name=NoAccountDisable}}
					<div class="checkbox">
						<p>
							<input type="checkbox" class="chk_noaccount" name="skipLogin" value="1" />
							<strong>{s name="LoginLabelNoAccount"}Kein Kundenkonto erstellen{/s}</strong>
						</p>
					</div>
				{/if}
				<input type="submit" class="button-right large register_now" value="{s name='LoginLinkRegister'}{/s}" />
			</form>
		</div>
	</div>
	{/block *}
	
	{* Existing customer *}
	{block name='frontend_account_login_customer'}
	<div class="register--existing-customer panel">
    	<h2 class="panel--title is--underline">{s name="LoginHeaderExistingCustomer"}{/s}</h2>
    	<div class="panel--body is--wide">
	        <form name="sLogin" method="post" action="{url action=login}">
	            {if $sTarget}<input name="sTarget" type="hidden" value="{$sTarget|escape}" />{/if}
	                <div class="register--login-description">{s name="LoginHeaderFields"}{/s}</div>

	                <div class="register--login-email">
	                    <input name="email" placeholder="{s name='LoginLabelMail'}{/s}" type="text" tabindex="1" value="{$sFormData.email|escape}" id="email" class="register--login-field{if $sErrorFlag.email} has--error{/if}" />
					</div>

	                <div class="register--login-password">
	                    <input name="password" placeholder="{s name="LoginLabelPassword"}{/s}" type="password" tabindex="2" id="passwort" class="register--login-field{if $sErrorFlag.password} has--error{/if}" />
					</div>
	            
	           		 <div class="register--login-lostpassword">
	    				<a href="{url action=password}" title="{s name='LoginLinkLostPassword'}{/s}">
	    					{s name="LoginLinkLostPassword"}{/s}
	    				</a>
	    			</div>

					<div class="register--login-action">
	           			<button type="button" class="btn btn--primary" name="Submit">{s name='LoginLinkLogon'}{/s} <i class="icon--arrow-right is--small"></i></button>
	            	</div>
	        </form>
    	</div>
    </div>
    {/block}

</div>
