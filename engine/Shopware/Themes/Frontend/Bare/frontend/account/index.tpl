{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{assign var='sBreadcrumb' value=[['name'=>"{s name='AccountTitle'}{/s}", 'link' =>{url action='index'}]]}
{/block}

{block name="frontend_index_left_categories_my_account"}{/block}

{block name="frontend_index_left_categories" prepend}
	{block name="frontend_account_sidebar"}
		{include file="frontend/account/sidebar.tpl"}
	{/block}
{/block}

{* Account Main Content *}
{block name='frontend_index_content'}
	<div class="content block account--content">

		{* Success messages *}
		{block name="frontend_account_index_success_messages"}
			{include file="frontend/account/success_messages.tpl"}
		{/block}

		{* Error messages *}
		{block name='frontend_account_index_error_messages'}
			{if $sErrorMessages}
				<div class="account--error">
					{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
				</div>
			{/if}
		{/block}

		{block name="frontend_account_index_welcome"}
			<div class="account--welcome panel">
				<h1 class="panel--title">{s name='AccountHeaderWelcome'}{/s}, {$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}</h1>
				<div class="panel--body is--wide">
					<p>{s name='AccountHeaderInfo'}{/s}</p>
				</div>
			</div>
		{/block}

		{* General user informations *}
		{block name="frontend_account_index_info"}
			<div class="account--info panel has--border">

				<h2 class="panel--title is--underline">{s name="AccountHeaderBasic"}{/s}</h2>

				<div class="panel--body is--wide">
					<p>
						{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
						{$sUserData.additional.user.email}
					</p>
				</div>

				<div class="panel--actions is--wide">
					<a href="#account--password"
					   class="btn btn--secondary is--small btn--password"
					   data-collapseTarget="#account--password"
					   data-closeSiblings="true"
					   data-scrollTarget="#account--password">
						{s name="AccountLinkChangePassword"}{/s}
					</a>
					<a href="#account--email"
					   class="btn btn--secondary is--small btn--email"
					   data-collapseTarget="#account--email"
					   data-closeSiblings="true"
					   data-scrollTarget="#account--email">
						{s name='AccountLinkChangeMail'}{/s}
					</a>
				</div>
			</div>
		{/block}

		{* Payment informations *}
		{block name="frontend_account_index_payment_method"}
			<div class="account--payment panel has--border">

				<h2 class="panel--title is--underline">{s name="AccountHeaderPayment"}{/s}</h2>

				<div class="panel--body is--wide">
					<p>
						<strong>{$sUserData.additional.payment.description}</strong><br />

						{if !$sUserData.additional.payment.esdactive}
							{s name="AccountInfoInstantDownloads"}{/s}
						{/if}
					</p>
				</div>

				<div class="panel--actions is--wide">
					<a href="{url controller='account' action='payment'}"
					   title="{s name='AccountLinkChangePayment'}{/s}"
					   class="btn btn--secondary is--small">
						{s name='AccountLinkChangePayment'}{/s}
					</a>
				</div>
			</div>
		{/block}

		{* Set new password *}
		{block name="frontend_account_index_password"}
			<div id="account--password" class="account--password panel has--border password{if $sErrorFlag.password || $sErrorFlag.passwordConfirmation || $sErrorFlag.currentPassword} is--block{/if}">

				<h2 class="panel--title is--underline">{s name='AccountLinkChangePassword'}{/s}</h2>

				{block name="frontend_account_index_change_password"}
					<form method="post" action="{url action=saveAccount}">
						<div class="panel--body is--wide">
							{if {config name=accountPasswordCheck}}
								<p>
									<input name="currentPassword" type="password" id="currentPassword" placeholder="{s name="AccountLabelCurrentPassword"}Ihr aktuelles Passwort*:{/s}" class="{if $sErrorFlag.currentPassword}has--error{/if}" />
								</p>
							{/if}
							<p>
								<input name="password" type="password" id="newpwd" placeholder="{s name="AccountLabelNewPassword"}{/s}" class="{if $sErrorFlag.password}has--error{/if}" />
							</p>
							<p>
								<input name="passwordConfirmation" id="newpwdrepeat" type="password" placeholder="{s name="AccountLabelRepeatPassword"}{/s}" class="{if $sErrorFlag.passwordConfirmation}has--error{/if}" />
							</p>
						</div>

						<div class="panel--actions is--wide">
							<input type="submit" value="{s name='AccountLinkChangePassword'}{/s}" class="btn btn--secondary is--small" />
						</div>
					</form>
				{/block}
			</div>
		{/block}

		{* Edit mail address *}
		{block name="frontend_account_index_email"}
			<div id="account--email" class="account--email panel has--border email{if $sErrorFlag.email || $sErrorFlag.emailConfirmation} is--block{/if}">

				<h2 class="panel--title is--underline">{s name='AccountLinkChangeMail'}{/s}</h2>

				{block name="frontend_account_index_change_email"}
					<form method="post" action="{url action=saveAccount}">
						<div class="panel--body is--wide">
							{if {config name=accountPasswordCheck}}
								<p>
									<input name="currentPassword" type="password" id="emailPassword" placeholder="{s name="AccountLabelCurrentPassword"}Ihr aktuelles Passwort*:{/s}" class="{if $sErrorFlag.currentPassword}has--error{/if}" />
								</p>
							{/if}
							<p>
								<input name="email" type="text" id="newmail" placeholder="{s name="AccountLabelNewMail"}{/s}" class="{if $sErrorFlag.email}has--error{/if}" />
							</p>
							<p>
								<input name="emailConfirmation" type="text" id="neweailrepeat" placeholder="{s name="AccountLabelMail"}{/s}" class="{if $sErrorFlag.emailConfirmation}has--error{/if}" />
							</p>
						</div>

						<div class="panel--actions is--wide">
							<input type="submit" value="{s name='AccountLinkChangeMail'}{/s}" class="btn btn--secondary is--small" />
						</div>
					</form>
				{/block}
			</div>
		{/block}

		{* Newsletter settings *}
		{block name='frontend_account_index_newsletter_settings'}
			<div class="account--newsletter panel has--border newsletter">

				<h2 class="panel--title is--underline">{s name="AccountHeaderNewsletter"}{/s}</h2>

				<div class="panel--body is--wide">
					<form name="frmRegister" method="post" action="{url action=saveNewsletter}">
						<fieldset>
							<p>
								<input type="checkbox" name="newsletter" value="1" id="newsletter" data-auto-submit="true" {if $sUserData.additional.user.newsletter}checked="checked"{/if} />
								<label for="newsletter">
									{s name="AccountLabelWantNewsletter"}{/s}
								</label>
							</p>
						</fieldset>
					</form>
				</div>
			</div>
		{/block}

		{* Addresses *}
		{block name="frontend_account_index_primary_billing"}
			<div class="account--billing panel has--border">

				<h2 class="panel--title is--underline">{s name="AccountHeaderPrimaryBilling"}{/s}</h2>

				<div class="panel--body is--wide">
					{if $sUserData.billingaddress.company}
						<p>
							{$sUserData.billingaddress.company}{if $sUserData.billingaddress.department} - {$sUserData.billingaddress.department}{/if}
						</p>
					{/if}
					<p>
						{if $sUserData.billingaddress.salutation eq "mr"}{s name="AccountSalutationMr"}{/s}{else}{s name="AccountSalutationMs"}{/s}{/if}
						{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
						{$sUserData.billingaddress.street} {$sUserData.billingaddress.streetnumber}<br />
						{if $sUserData.billingaddress.additional_address_line1}{$sUserData.billingaddress.additional_address_line1}<br />{/if}
						{if $sUserData.billingaddress.additional_address_line2}{$sUserData.billingaddress.additional_address_line2}<br />{/if}
						{$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}<br />
						{if $sUserData.additional.state.name}{$sUserData.additional.state.name}<br />{/if}
						{$sUserData.additional.country.countryname}
					</p>
				</div>

				<div class="panel--actions is--wide">
					<a href="{url action=selectBilling}" title="{s name='AccountLinkSelectBilling'}{/s}" class="btn btn--secondary is--small">
						{s name="AccountLinkSelectBilling"}{/s}
					</a>
					<a href="{url action=billing}" title="{s name='AccountLinkChangeBilling'}{/s}" class="btn btn--secondary is--small">
						{s name="AccountLinkChangeBilling"}{/s}
					</a>
				</div>
			</div>
		{/block}

		{block name="frontend_account_index_primary_shipping"}
			<div class="account--shipping panel has--border">

				<h2 class="panel--title is--underline">{s name="AccountHeaderPrimaryShipping"}{/s}</h2>

				<div class="panel--body is--wide">
					{if $sUserData.shippingaddress.company}
						<p>
							{$sUserData.shippingaddress.company}{if $sUserData.shippingaddress.department} - {$sUserData.shippingaddress.department}{/if}
						</p>
					{/if}
					<p>
						{if $sUserData.shippingaddress.salutation eq "mr"}{s name="AccountSalutationMr"}{/s}{else}{s name="AccountSalutationMs"}{/s}{/if}
						{$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br />
						{$sUserData.shippingaddress.street} {$sUserData.shippingaddress.streetnumber}<br />
						{if $sUserData.shippingaddress.additional_address_line1}{$sUserData.shippingaddress.additional_address_line1}<br />{/if}
						{if $sUserData.shippingaddress.additional_address_line2}{$sUserData.shippingaddress.additional_address_line2}<br />{/if}
						{$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}<br />
						{if $sUserData.additional.stateShipping.name}{$sUserData.additional.stateShipping.name}<br />{/if}
						{$sUserData.additional.countryShipping.countryname}
					</p>
				</div>

				<div class="panel--actions is--wide">
					<a href="{url action=selectShipping}" title="{s name='AccountLinkSelectShipping'}{/s}" class="btn btn--secondary is--small">
						{se name="AccountLinkSelectShipping"}{/se}
					</a>
					<a href="{url action=shipping}" title="{s name='AccountLinkChangeShipping'}{/s}" class="btn btn--secondary is--small">
						{se name="AccountLinkChangeShipping"}{/se}
					</a>
				</div>
			</div>
		{/block}

	</div>
{/block}

{* @deprecated: Account menu moved to the sidebar.tpl *}
{block name='frontend_index_content_right'}{/block}