{extends file="frontend/index/index.tpl"}

{* Left sidebar *}
{block name="frontend_index_content_left"}
	{include file='frontend/index/left.tpl'}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="grid_16 tellafriend push_2" id="center">

		{if $sSuccess}
		    <div class="success">
		        <strong>{s name='TellAFriendHeaderSuccess'}{/s}</strong>
		    </div>
		{else}
			{if $sError}
			    <div class="error">
			        <strong>{s name='TellAFriendInfoFields'}{/s}</strong>
			    </div>
			{/if}
		{/if}

        {if !$sSuccess}

		<form name="mailtofriend" action="" method="post">
			<input type="hidden" name="sMailTo" value="1" />
			<input type="hidden" name="sDetails" value="{$sArticle.articleID}" />

			{* Validation errors *}
			{if $error}
			    <div class="error">
			   		<p>{foreach from=$error item=error_item}{$error_item}</p>{/foreach}
			    </div>
			{/if}

			<h2 class="headingbox_dark largesize">
				<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">{$sArticle.articleName}</a> {s name='TellAFriendHeadline'}{/s}
			</h2>

			<fieldset>
			   <div>
			    	<label>{s name='TellAFriendLabelName'}{/s}*:</label>
			    	<input name="sName" type="text" id="txtName" class="text" value="{$sName|escape}" />
			    	<div class="clear">&nbsp;</div>
			    </div>
			    <div>
			    	<label>{s name='TellAFriendLabelMail'}{/s}</label>
			    	<input name="sMail" type="text" id="txtMail" class="text" value="{$sMail|escape}" />
			    	<div class="clear">&nbsp;</div>
			    </div>
			    <div>
			    	<label>{s name='TellAFriendLabelFriendsMail'}{/s}*:</label>
			    	<input name="sRecipient" type="text" id="txtMailTo" class="text" value="{$sRecipient|escape}" />
			    	<div class="clear">&nbsp;</div>
			    </div>
			    <div class="textarea">
			    	<label for="comment">{s name='TellAFriendLabelComment'}{/s}</label>
			    	<textarea name="sComment" id="comment" >{$sComment|escape}</textarea>
				</div>

				<div class="space">&nbsp;</div>

				<div class="captcha grid_4">
                    <div class="captcha-placeholder" data-src="{url module=widgets controller=Captcha action=refreshCaptcha}"></div>
				</div>

				<div class="code">
					<label for="sCaptcha">{s name='TellAFriendLabelCaptcha'}{/s}</label>
					<input type="text" name="sCaptcha" class="text{if $sErrorFlag.sCaptcha} instyle_error{/if}" />
				</div>

				<div class="clear">&nbsp;</div>

				<div class="buttons">
					<a href="{$sArticle.linkDetails}" class="button-left large left">{s name='TellAFriendLinkBack'}{/s}</a>
					<input type="submit" value="{s name='TellAFriendActionSubmit'}{/s}" class="button-right large right" />
					<div class="space">&nbsp;</div>
				</div>

			</fieldset>
		</form>{/if}
		<div class="doublespace">&nbsp;</div>
	</div>
{/block}

{* Empty right sidebar *}
{block name='frontend_index_content_right'}{/block}
