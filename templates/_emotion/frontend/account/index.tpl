{extends file='frontend/index/index.tpl'}

{block name="frontend_index_content_left"}{/block}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{assign var='sBreadcrumb' value=[['name'=>"{s name='AccountTitle'}{/s}", 'link' =>{url action='index'}]]}
{/block}

{* Breadcrumb *}
{block name='frontend_index_breadcrumb'}
	<div id="breadcrumb" class="account">
		{se name='AccountHeaderWelcome'}{/se}, <strong style="font-weight:bold;">{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}</strong>
	</div>
{/block}

{block name='frontend_index_content'}
<div class="grid_16 account" id="center">
	<div class="cat_text">
		<div class="inner_container">
			<h1>{se name='AccountHeaderWelcome'}{/se}, {$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}</h1>
			<p>
				{se name='AccountHeaderInfo'}{/se}
			</p>
		</div>
	</div>
	
	<div class="doublespace">&nbsp;</div>
	
	{* Success messages *}
	{block name="frontend_account_index_success_messages"}
		{include file="frontend/account/success_messages.tpl"}
	{/block}
	
	{* Error messages *}
	{block name='frontend_account_index_error_messages'}
	{if $sErrorMessages}
	<div class="grid_16 error_msg">
		{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
	</div>
	{/if}
	{/block}
	
	{* General user informations *}
	{block name="frontend_account_index_info"}
	<div id="userinformations" class="grid_8 first">
		<h2 class="headingbox_dark largesize">{se name="AccountHeaderBasic"}{/se}</h2>
		<div class="inner_container">
			<p>
				{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
				{$sUserData.additional.user.email}
			</p>
			<div class="change">
				<a href="#" class="button-middle small change_password">{se name="AccountLinkChangePassword"}{/se}</a>
				<a href="#" class="button-middle small change_mail">{se name='AccountLinkChangeMail'}{/se}</a>
			</div>
		</div>
	</div>
	{/block}
		
	{* Payment informations *}
	{block name="frontend_account_index_payment_method"}
	<div class="grid_8 last" id="selected_payment">
		<h2 class="headingbox_dark largesize">{se name="AccountHeaderPayment"}{/se}</h2>
		<div class="inner_container">
			<p>
				<strong>{$sUserData.additional.payment.description}</strong><br />
	            
	            {if !$sUserData.additional.payment.esdactive && {config name="showEsd"}}
	                {se name="AccountInfoInstantDownloads"}{/se}
	            {/if}
	        </p>
	        
	        <div class="change">
				<a href="{url controller='account' action='payment'}" title="{s name='AccountLinkChangePayment'}{/s}" class="button-middle small">
					{se name='AccountLinkChangePayment'}{/se}
				</a>
			</div>
		</div>
	</div>
	{/block}
	
	{* Set new password *}
	 <div class="grid_16 first password{if $sErrorFlag.password || $sErrorFlag.passwordConfirmation} displayblock{/if}">
	 	<div class="doublespace">&nbsp;</div>
        {block name="frontend_account_index_change_password"}
         <form method="post" action="{url action=saveAccount}">
             <h2 class="headingbox_dark largesize">{se name='AccountLinkChangePassword'}{/se}</h2>
             <div class="inner_container">
                 {if {config name=accountPasswordCheck}}
                 <p>
                     <label for="currentPassword">{se name="AccountLabelCurrentPassword"}Ihr aktuelles Passwort*:{/se}</label>
                     <input name="currentPassword" type="password" id="currentPassword" class="text {if $sErrorFlag.currentPassword}instyle_error{/if}" />
                 </p>
                 {/if}
                 {* New password *}
                 <p>
                     <label for="newpwd">{se name="AccountLabelNewPassword"}{/se}</label>
                     <input name="password" type="password" id="newpwd" class="text {if $sErrorFlag.password}instyle_error{/if}" />
                 </p>

	    		{* Repeat new Password *}
                 <p>
                     <label for="newpwdrepeat">{se name="AccountLabelRepeatPassword"}{/se}</label>
                     <input name="passwordConfirmation" id="newpwdrepeat" type="password" class="text {if $sErrorFlag.passwordConfirmation}instyle_error{/if}" />
                 </p>

                 <input type="submit" value="{s name='AccountLinkChangePassword'}{/s}" class="button-right small_right" />
             </div>
         </form>
        {/block}
	</div>
	
	{* Edit mail address *}
	<div class="grid_16 first email{if $sErrorFlag.email || $sErrorFlag.emailConfirmation} displayblock{/if}">
	 	<div class="doublespace">&nbsp;</div>
		{block name="frontend_account_index_change_email"}
		<form method="post" action="{url action=saveAccount}">
			<h2 class="headingbox_dark largesize">{se name='AccountLinkChangeMail'}{/se}</h2>
    		<div class="inner_container">
                {if {config name=accountPasswordCheck}}
                <p>
                    <label for="emailPassword">{se name="AccountLabelCurrentPassword"}Ihr aktuelles Passwort*:{/se}</label>
                    <input name="currentPassword" type="password" id="emailPassword" class="text {if $sErrorFlag.currentPassword}instyle_error{/if}" />
                </p>
                {/if}
	    		<p>
	    			<label for="newmail">{se name="AccountLabelNewMail"}{/se}*:</label>
	    			<input name="email" type="text" id="newmail" class="text {if $sErrorFlag.email}instyle_error{/if}" />
	    		</p>
	    		<p>
	    			<label for="newmailrepeat">{se name="AccountLabelMail"}{/se}*:</label>
	    			<input name="emailConfirmation" id="neweailrepeat" type="text" class="text {if $sErrorFlag.emailConfirmation}instyle_error{/if}" />
	    		</p>

	    		<input type="submit" value="{s name='AccountLinkChangeMail'}{/s}" class="button-right small_right" />
    		</div>
    	</form>
    	{/block}
	</div>
	
	<div class="doublespace">&nbsp;</div>
	
	{* Newsletter settings *}
	{block name='frontend_account_index_newsletter_settings'}
	<div class="grid_16 newsletter first last">
		<form name="frmRegister" method="post" action="{url action=saveNewsletter}">
			<h2 class="headingbox_dark largesize">{se name="AccountHeaderNewsletter"}{/se}</h2>
			<div class="inner_container">
				<div class="form">
					<fieldset>
			        	<p>
			        		<input class="auto_submit" type="checkbox" name="newsletter" value="1" id="newsletter" {if $sUserData.additional.user.newsletter}checked="checked"{/if} class="chkbox" />
			        		<label for="newsletter" class="chklabel">
			        			{se name="AccountLabelWantNewsletter"}{/se}
			        		</label>
			        	</p>
			        </fieldset>
			    </div>
		    </div>
		</form>
	</div>
	{/block}
	
	<div class="doublespace">&nbsp;</div>
	
	{* Addresses *}
	{block name="frontend_account_index_primary_billing"}
	<div class="billing grid_8 first">
		<h2 class="headingbox_dark largesize">{se name="AccountHeaderPrimaryBilling"}{/se}</h2>
		<div class="inner_container">
			{if $sUserData.billingaddress.company}
			<p>
				{$sUserData.billingaddress.company}{if $sUserData.billingaddress.department} - {$sUserData.billingaddress.department}{/if}
			</p>
			{/if}
			<p>
				{if $sUserData.billingaddress.salutation eq "mr"}{se name="AccountSalutationMr"}{/se}{else}{se name="AccountSalutationMs"}{/se}{/if}
				{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
				{$sUserData.billingaddress.street}<br />
				{if $sUserData.billingaddress.additional_address_line1}{$sUserData.billingaddress.additional_address_line1}<br />{/if}
				{if $sUserData.billingaddress.additional_address_line2}{$sUserData.billingaddress.additional_address_line2}<br />{/if}
				{$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}<br />
				{if $sUserData.additional.state.statename}{$sUserData.additional.state.statename}<br />{/if}
				{$sUserData.additional.country.countryname}
			</p>
			<div class="change">
				<a href="{url action=selectBilling}" title="{s name='AccountLinkSelectBilling'}{/s}" class="button-middle small">
					{se name="AccountLinkSelectBilling"}{/se}
				</a>
				<a href="{url action=billing}" title="{s name='AccountLinkChangeBilling'}{/s}" class="button-middle small change">
					{se name="AccountLinkChangeBilling"}{/se}
				</a>
			</div>
		</div>
	</div>
	{/block}
	
	{block name="frontend_account_index_primary_shipping"}
	<div class="shipping grid_8 last">
		<h2 class="headingbox_dark largesize">{se name="AccountHeaderPrimaryShipping"}{/se}</h2>
		<div class="inner_container">
			{if $sUserData.shippingaddress.company}
			<p>
	        	{$sUserData.shippingaddress.company}{if $sUserData.shippingaddress.department} - {$sUserData.shippingaddress.department}{/if}
	        </p>
			{/if}
	        <p>
	        {if $sUserData.shippingaddress.salutation eq "mr"}{se name="AccountSalutationMr"}{/se}{else}{se name="AccountSalutationMs"}{/se}{/if}
	    	{$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br />
			{$sUserData.shippingaddress.street}<br />
			{if $sUserData.shippingaddress.additional_address_line1}{$sUserData.shippingaddress.additional_address_line1}<br />{/if}
			{if $sUserData.shippingaddress.additional_address_line2}{$sUserData.shippingaddress.additional_address_line2}<br />{/if}
			{$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}<br />
			{if $sUserData.additional.stateShipping.statename}{$sUserData.additional.stateShipping.statename}<br />{/if}
			{$sUserData.additional.countryShipping.countryname}
			</p>
			
			<div class="change">
				<a href="{url action=selectShipping}" title="{s name='AccountLinkSelectShipping'}{/s}" class="button-middle small">
					{se name="AccountLinkSelectShipping"}{/se}
				</a>
				<a href="{url action=shipping}" title="{s name='AccountLinkChangeShipping'}{/s}" class="button-middle small change">
					{se name="AccountLinkChangeShipping"}{/se}
				</a>
			</div>
		</div>
	</div>
	{/block}
	<div class="doublespace">&nbsp;</div>
</div>
{/block}

{block name='frontend_index_content_right'}
	<div id="right_account" class="grid_4 last">
		{include file="frontend/account/content_right.tpl"}
	</div>
{/block}