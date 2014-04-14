{extends file="frontend/index/index.tpl"}

{* Left sidebar *}
{block name="frontend_index_content_left"}
	{include file='frontend/index/sidebar.tpl'}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="content block tellafriend--content">

		{if $sSuccess}
			<div class="alert success">
				<strong>{s name='TellAFriendHeaderSuccess'}{/s}</strong>
			</div>
		{else}
			{if $sError}
				<div class="alert error">
					<strong>{s name='TellAFriendInfoFields'}{/s}</strong>
				</div>
			{/if}
		{/if}

		{block name='frontend_tellafriend_success'}
			{if !$sSuccess}

				{block name='frontend_tellafriend_form'}
					<form name="mailtofriend" class="panel tellafriend--form has--border" action="" method="post">
					<input type="hidden" name="sMailTo" value="1"/>
					<input type="hidden" name="sDetails" value="{$sArticle.articleID}"/>

					{* Validation errors *}
					{if $error}
						<div class="alert error">
							<p>{foreach $error as $error_item}{$error_item}</p>{/foreach}
						</div>
					{/if}

					{block name='frontend_tellafriend_headline'}
						<h2 class="panel--title is--underline">
							<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">{$sArticle.articleName}</a> {s name='TellAFriendHeadline'}{/s}
						</h2>
					{/block}

					<div class="panel--body is--wide">

						{* TellAFriend name *}
						{block name='frontend_tellafriend_field_name'}
							<div class="tellafriend--name">
								<input name="sName" type="text" class="tellafriend--field" placeholder="{s name='TellAFriendLabelName'}{/s}*:" value="{$sName|escape}"/>
							</div>
						{/block}

						{* TellAFriend email address *}
						{block name='frontend_tellafriend_field_email'}
							<div class="tellafriend--email">
								<input name="sMail" type="email" class="tellafriend--field" placeholder="{s name='TellAFriendLabelMail'}{/s}" value="{$sMail|escape}"/>
							</div>
						{/block}

						{* TellAFriend receiver email address *}
						{block name='frontend_tellafriend_field_friendsemail'}
							<div class="tellafriend--receiver-email">
								<input name="sRecipient" type="email" class="tellafriend--field" placeholder="{s name='TellAFriendLabelFriendsMail'}{/s}*:" value="{$sRecipient|escape}"/>
							</div>
						{/block}

						{* TellAFriend comment *}
						{block name='frontend_tellafriend_field_comment'}
							<div class="tellafriend--comment">
								<textarea name="sComment" class="tellafriend--field" placeholder="{s name='TellAFriendLabelComment'}{/s}">{$sComment|escape}</textarea>
							</div>
						{/block}

						{* Captcha *}
						{block name='frontend_tellafriend_captcha'}
							<div class="captcha">

								{* Deferred loading of the captcha image *}
								{block name='frontend_tellafriend_captcha_placeholder'}
									<div class="captcha--placeholder" data-src="{url module=widgets controller=Captcha action=refreshCaptcha}"></div>
								{/block}

								{block name='frontend_tellafriend_captcha_label'}
									<strong class="captcha--notice">{s name="TellAFriendLabelCaptcha"}{/s}</strong>
								{/block}

								{block name='frontend_tellafriend_captcha_field_code'}
									<div class="code">
										<input type="text" name="sCaptcha" class="tellafriend--field{if $sErrorFlag.sCaptcha} has--error{/if}"/>
									</div>
								{/block}

							</div>
						{/block}

					{* Notice that all fields which contains a star symbole needs to be filled out *}
					{block name='frontend_tellafriend_captcha_notice'}
						<p class="review--notice">
							{s name="TellAFriendMarkedInfoFields"}{/s}
						</p>
					{/block}

					{* Send recommendation button *}
					{block name='frontend_tellafriend_captcha_code_actions'}
						<div class="buttons">
							<a href="{$sArticle.linkDetails}" class="btn btn--secondary is--bold">{s name='TellAFriendLinkBack'}{/s}</a>

							<button type="submit" class="btn btn--primary">
								{s name='TellAFriendActionSubmit'}{/s} <i class="icon--arrow-right"></i>
							</button>
						</div>
					{/block}
					</div>
				</form>
				{/block}

			{/if}
		{/block}
	</div>
{/block}

{* Empty right sidebar *}
{block name='frontend_index_content_right'}{/block}
