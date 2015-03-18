{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{assign var='sBreadcrumb' value=[['name'=>"{s name='AccountTitle'}{/s}", 'link' =>{url action='index'}]]}
{/block}

{block name="frontend_index_left_categories_my_account"}{/block}

{* Account Sidebar *}
{block name="frontend_index_left_categories" prepend}
	{block name="frontend_account_sidebar"}
		{include file="frontend/account/sidebar.tpl"}
	{/block}
{/block}

{* Account Main Content *}
{block name="frontend_index_content"}
	<div class="content account--content">

		{* Success messages *}
		{block name="frontend_account_index_success_messages"}
			{include file="frontend/account/success_messages.tpl"}
		{/block}

		{* Error messages *}
		{block name="frontend_account_index_error_messages"}
			{if $sErrorMessages}
				<div class="account--error">
					{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
				</div>
			{/if}
		{/block}

		{* Welcome text *}
		{block name="frontend_account_index_welcome"}
			<div class="account--welcome panel">
				{block name="frontend_account_index_welcome_headline"}
					<h1 class="panel--title">{s name='AccountHeaderWelcome'}{/s}, {$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}</h1>
				{/block}

				{block name="frontend_account_index_welcome_content"}
					<div class="panel--body is--wide">
						<p>{s name='AccountHeaderInfo'}{/s}</p>
					</div>
				{/block}
			</div>
		{/block}

		{* General user informations *}
		{block name="frontend_account_index_info"}
			<div class="account--info account--box panel has--border is--rounded">

				{block name="frontend_account_index_info_headline"}
					<h2 class="panel--title is--underline">{s name="AccountHeaderBasic"}{/s}</h2>
				{/block}

				{block name="frontend_account_index_info_content"}
					<div class="panel--body is--wide">
						<p>
							{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
							{$sUserData.additional.user.email}
						</p>
					</div>
				{/block}

				{block name="frontend_account_index_info_actions"}
					<div class="panel--actions is--wide">
						<a href="#account--password"
						   class="btn is--small btn--password"
						   data-collapseTarget="#account--password"
						   data-closeSiblings="true"
						   data-scrollTarget="#account--password">
							{s name="AccountLinkChangePassword"}{/s}
						</a>
						<a href="#account--email"
						   class="btn is--small btn--email"
						   data-collapseTarget="#account--email"
						   data-closeSiblings="true"
						   data-scrollTarget="#account--email">
							{s name='AccountLinkChangeMail'}{/s}
						</a>
					</div>
				{/block}
			</div>
		{/block}

		{* Payment information *}
		{block name="frontend_account_index_payment_method"}
			<div class="account--payment account--box panel has--border is--rounded">

				{block name="frontend_account_index_payment_method_headline"}
					<h2 class="panel--title is--underline">{s name="AccountHeaderPayment"}{/s}</h2>
				{/block}

				{block name="frontend_account_index_payment_method_content"}
					<div class="panel--body is--wide">
						<p>
							<strong>{$sUserData.additional.payment.description}</strong><br />

							{if !$sUserData.additional.payment.esdactive && {config name="showEsd"}}
								{s name="AccountInfoInstantDownloads"}{/s}
							{/if}
						</p>
					</div>
				{/block}

				{block name="frontend_account_index_payment_method_actions"}
					{$paymentMethodTitle = {"{s name='AccountLinkChangePayment'}{/s}"|escape}}

					<div class="panel--actions is--wide">
						<a href="{url controller='account' action='payment'}"
						   title="{$paymentMethodTitle|escape}"
						   class="btn is--small">
							{s name='AccountLinkChangePayment'}{/s}
						</a>
					</div>
				{/block}
			</div>
		{/block}

		{* Set new password *}
		{block name="frontend_account_index_password"}
			<div id="account--password" class="account--password account--box panel has--border is--rounded password{if $sErrorFlag.password || $sErrorFlag.passwordConfirmation} is--collapsed{/if}">

				{block name="frontend_account_index_password_headline"}
					<h2 class="panel--title is--underline">{s name='AccountLinkChangePassword'}{/s}</h2>
				{/block}

				{block name="frontend_account_index_change_password"}
					<form method="post" action="{url action=saveAccount}">

						{block name="frontend_account_index_password_content"}
							<div class="panel--body is--wide">
								{if {config name=accountPasswordCheck}}
									<p>
										<input name="currentPassword" type="password" id="currentPassword" placeholder="{s name="AccountLabelCurrentPassword2"}{/s}{s name="Star" namespace="frontend/listing/box_article"}{/s}" class="{if $sErrorFlag.currentPassword}has--error{/if}" />
									</p>
								{/if}
								<p>
									<input name="password" type="password" id="newpwd" placeholder="{s name="AccountLabelNewPassword2"}{/s}{s name="Star" namespace="frontend/listing/box_article"}{/s}" class="{if $sErrorFlag.password}has--error{/if}" />
								</p>
								<p>
									<input name="passwordConfirmation" id="newpwdrepeat" type="password" placeholder="{s name="AccountLabelRepeatPassword2"}{/s}{s name="Star" namespace="frontend/listing/box_article"}{/s}" class="{if $sErrorFlag.passwordConfirmation}has--error{/if}" />
								</p>
							</div>
						{/block}

						{block name="frontend_account_index_password_actions"}
							<div class="panel--actions is--wide">
								<input type="submit" value="{s name='AccountLinkChangePassword'}{/s}" class="btn is--primary is--small" />
							</div>
						{/block}
					</form>
				{/block}
			</div>
		{/block}

		{* Edit mail address *}
		{block name="frontend_account_index_email"}
			<div id="account--email" class="account--email account--box panel has--border is--rounded email{if $sErrorFlag.email || $sErrorFlag.emailConfirmation} is--collapsed{/if}">

				{block name="frontend_account_index_email_headline"}
					<h2 class="panel--title is--underline">{s name='AccountLinkChangeMail'}{/s}</h2>
				{/block}

				{block name="frontend_account_index_change_email"}
					<form method="post" action="{url action=saveAccount}">

						{block name="frontend_account_index_email_content"}
							<div class="panel--body is--wide">
								{if {config name=accountPasswordCheck}}
									<p>
										<input name="currentPassword" type="password" id="emailPassword" placeholder="{s name="AccountLabelCurrentPassword2"}{/s}{s name="Star" namespace="frontend/listing/box_article"}{/s}" class="{if $sErrorFlag.currentPassword}has--error{/if}" />
									</p>
								{/if}
								<p>
									<input name="email" type="email" id="newmail" placeholder="{s name="AccountLabelNewMail"}{/s}{s name="Star" namespace="frontend/listing/box_article"}{/s}" class="{if $sErrorFlag.email}has--error{/if}" />
								</p>
								<p>
									<input name="emailConfirmation" type="email" id="neweailrepeat" placeholder="{s name="AccountLabelMail"}{/s}{s name="Star" namespace="frontend/listing/box_article"}{/s}" class="{if $sErrorFlag.emailConfirmation}has--error{/if}" />
								</p>
							</div>
						{/block}

						{block name="frontend_account_index_email_actions"}
							<div class="panel--actions is--wide">
								<input type="submit" value="{s name='AccountLinkChangeMail'}{/s}" class="btn is--primary is--small" />
							</div>
						{/block}
					</form>
				{/block}
			</div>
		{/block}

		{* Billing addresses *}
		{block name="frontend_account_index_primary_billing"}
			<div class="account--billing account--box panel has--border is--rounded">

				{block name="frontend_account_index_primary_billing_headline"}
					<h2 class="panel--title is--underline">{s name="AccountHeaderPrimaryBilling"}{/s}</h2>
				{/block}

				{block name="frontend_account_index_primary_billing_content"}
					<div class="panel--body is--wide">
						{if $sUserData.billingaddress.company}
							<p>
								{$sUserData.billingaddress.company}{if $sUserData.billingaddress.department} - {$sUserData.billingaddress.department}{/if}
							</p>
						{/if}
						<p>
							{if $sUserData.billingaddress.salutation eq "mr"}
								{s name="AccountSalutationMr"}{/s}
							{else}
								{s name="AccountSalutationMs"}{/s}
							{/if}
							{$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
							{$sUserData.billingaddress.street}<br />
							{if $sUserData.billingaddress.additional_address_line1}{$sUserData.billingaddress.additional_address_line1}<br />{/if}
							{if $sUserData.billingaddress.additional_address_line2}{$sUserData.billingaddress.additional_address_line2}<br />{/if}
                            {if {config name=showZipBeforeCity}}{$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}{else}{$sUserData.billingaddress.city} {$sUserData.billingaddress.zipcode}{/if}<br />
							{if $sUserData.additional.state.statename}{$sUserData.additional.state.statename}<br />{/if}
							{$sUserData.additional.country.countryname}
						</p>
					</div>
				{/block}

				{block name="frontend_account_index_primary_billing_actions"}
					<div class="panel--actions is--wide">
						<a href="{url action=selectBilling}" title="{"{s name='AccountLinkSelectBilling'}{/s}"|escape}" class="btn is--small">
							{s name="AccountLinkSelectBilling"}{/s}
						</a>
						<a href="{url action=billing}" title="{"{s name='AccountLinkChangeBilling'}{/s}"|escape}" class="btn is--small">
							{s name="AccountLinkChangeBilling"}{/s}
						</a>
					</div>
				{/block}
			</div>
		{/block}

		{* Shipping addresses *}
		{block name="frontend_account_index_primary_shipping"}
			<div class="account--shipping account--box panel has--border is--rounded">

				{block name="frontend_account_index_primary_shipping_headline"}
					<h2 class="panel--title is--underline">{s name="AccountHeaderPrimaryShipping"}{/s}</h2>
				{/block}

				{block name="frontend_account_index_primary_shipping_content"}
					<div class="panel--body is--wide">
						{if $sUserData.shippingaddress.company}
							<p>
								{$sUserData.shippingaddress.company}{if $sUserData.shippingaddress.department} - {$sUserData.shippingaddress.department}{/if}
							</p>
						{/if}
						<p>
							{if $sUserData.shippingaddress.salutation eq "mr"}
								{s name="AccountSalutationMr"}{/s}
							{else}
								{s name="AccountSalutationMs"}{/s}
							{/if}
							{$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br />
							{$sUserData.shippingaddress.street}<br />
							{if $sUserData.shippingaddress.additional_address_line1}{$sUserData.shippingaddress.additional_address_line1}<br />{/if}
							{if $sUserData.shippingaddress.additional_address_line2}{$sUserData.shippingaddress.additional_address_line2}<br />{/if}
                            {if {config name=showZipBeforeCity}}{$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}{else}{$sUserData.shippingaddress.city} {$sUserData.shippingaddress.zipcode}{/if}<br />
							{if $sUserData.additional.stateShipping.statename}{$sUserData.additional.stateShipping.statename}<br />{/if}
							{$sUserData.additional.countryShipping.countryname}
						</p>
					</div>
				{/block}

				{block name="frontend_account_index_primary_shipping_actions"}
					<div class="panel--actions is--wide">
						<a href="{url action=selectShipping}" title="{"{s name='AccountLinkSelectShipping'}{/s}"|escape}" class="btn is--small">
							{s name="AccountLinkSelectShipping"}{/s}
						</a>
						<a href="{url action=shipping}" title="{"{s name='AccountLinkChangeShipping'}{/s}"|escape}" class="btn is--small">
							{s name="AccountLinkChangeShipping"}{/s}
						</a>
					</div>
				{/block}
			</div>
		{/block}

        {* Newsletter settings *}
        {block name="frontend_account_index_newsletter_settings"}
            <div class="account--newsletter account--box panel has--border is--rounded newsletter">

                {block name="frontend_account_index_newsletter_settings_headline"}
                <h2 class="panel--title is--underline">{s name="AccountHeaderNewsletter"}{/s}</h2>
                {/block}

                {block name="frontend_account_index_newsletter_settings_content"}
                    <div class="panel--body is--wide">
                        <form name="frmRegister" method="post" action="{url action=saveNewsletter}">
                            <fieldset>
                                <input type="checkbox" name="newsletter" value="1" id="newsletter" data-auto-submit="true" {if $sUserData.additional.user.newsletter}checked="checked"{/if} />
                                <label for="newsletter">
                                    {s name="AccountLabelWantNewsletter"}{/s}
                                </label>
                            </fieldset>
                        </form>
                    </div>
                {/block}
            </div>
		{/block}

	</div>
{/block}