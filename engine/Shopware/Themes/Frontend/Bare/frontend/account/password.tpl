{extends file='frontend/index/index.tpl'}

{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div class="content block account--password-reset">

		{* Error messages *}
		{block name='frontend_account_error_messages'}
			{if $sErrorMessages}
				<div class="account--error">
					{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
				</div>
			{/if}
		{/block}

		{if $sSuccess}
			{* Success message *}
			{block name='frontend_account_password_success'}
				{include file="frontend/_includes/messages.tpl" type="success" content="{s name='PasswordInfoSuccess'}{/s}"}
				<p><a href="javascript:window.history.back();" class="btn btn--secondary"><span>{s name="LoginBack"}{/s}</span></a></p>
			{/block}
		{else}
			{* Recover password *}
			{block name="frontend_account_password_reset"}
				<div class="panel has--border">

					{block name="frontend_account_password_reset_headline"}
						<h2 class="panel--title is--underline">{s name="PasswordHeader"}{/s}</h2>
					{/block}

					{block name='frontend_account_password_form'}
						{* Recover password form *}
						<form name="frmRegister" method="post" action="{url action=password}">

							{block name="frontend_account_password_reset_content"}
								<div class="panel--body is--wide is--align-center">
									<p>
										<label class="password-reset--label" for="email">{s name="PasswordLabelMail"}{/s}</label>
										<input name="email" type="email" required="required" aria-required="true" class="password-reset--input" />
									</p>
									<p>{s name="PasswordText"}{/s}</p>
								</div>
							{/block}

							{* Recover password actions *}
							{block name="frontend_account_password_reset_actions"}
								<div class="password-reset--actions panel--actions is--align-center">
									<input type="submit" class="btn btn--primary" value="{s name="PasswordSendAction"}Passwort anfordern{/s}" />
									<a href="javascript:window.history.back();" class="btn btn--secondary">{s name="PasswordLinkBack"}{/s}</a>
								</div>
							{/block}
						</form>
					{/block}
				</div>
			{/block}
		{/if}
	</div>
{/block}