{extends file="parent:frontend/account/login.tpl"}

{block name='frontend_account_login_error_messages' append}
<div class="grid_20 privateshopping">
	<h2 class="headingbox largesize">
		{s name="PrivateLoginWelcomeHeader"}Willkommen beim Demo Shopping Club{/s}
	</h2>
	
	{* Check if registration is allowed *}
	{if $privateShoppingConfiguration.registerlink}
		<div class="register-message message">
			<div class="inner-message">
				{s name="PrivateLoginRegisterInfo"}Benutzen Sie folgenden Link um einen Zugang zu diesem Shopping-Club anzufragen:{/s}
				
				<a href="{url controller=PrivateRegister action=index sValidation=$privateShoppingConfig.registergroup forceSecure}">
					{s name="PrivateLoginRegisterAction"}Zur Registrierung{/s}
				</a>
			</div>
		</div>
	{else}
		<div class="invite-message message">
			<div class="inner-message">
				{s name="PrivateLoginWelcomeMessage"}Einen Zugang erhalten Sie auf Einladung!{/s}
			</div>
		</div>
	{/if}
</div>
{/block}

{block name='frontend_account_login_customer'}

<div class="grid_10">
	<h2 class="headingbox_dark largesize">{se name="LoginHeaderExistingCustomer" namespace="frontend/account/login"}{/se}</h2>
	<div class="inner_container">
        <form name="sLogin" method="post" action="{url action=login}">
            {if $sTarget}<input name="sTarget" type="hidden" value="{$sTarget|escape}" />{/if}
            <fieldset>
                <p>{se name="LoginHeaderFields" namespace="frontend/account/login"}{/se}</p>
                <p>
                    <label for="email">{se name='LoginLabelMail' namespace="frontend/account/login"}{/se}</label>
                    <input name="email" type="text" tabindex="1" value="{$sFormData.email|escape}" id="email" class="text {if $sErrorFlag.email}instyle_error{/if}" />
                </p>
                <p class="none">
                    <label for="passwort">{se name="LoginLabelPassword" namespace="frontend/account/login"}{/se}</label>
                    <input name="password" type="password" tabindex="2" id="passwort" class="text {if $sErrorFlag.password}instyle_error{/if}" />
                </p>
            </fieldset>

            <p class="password">
    			<a href="{url action=password}" title="{s name='LoginLinkLostPassword' namespace="frontend/account/login"}{/s}">
    				{se name="LoginLinkLostPassword" namespace="frontend/account/login"}{/se}
    			</a>
    		</p>
            <div class="action">
           		<input class="button-middle small" type="submit" value="{s name='LoginLinkLogon' namespace="frontend/account/login"}{/s}" name="Submit"/>
            </div>
			
        </form>
	</div>
</div>
<div class="clear"></div>
{/block}