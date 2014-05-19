{block name='frontend_forms_elements'}
<form id="support" name="support" class="{$sSupport.class}" method="post" action="{url controller='ticket' action='index' id=$id}" enctype="multipart/form-data">
<input type="hidden" name="forceMail" value="{$forceMail|escape}" >
{/block}
		{if $sSupport.sErrors.e || $sSupport.sErrors.v}
			{$errorContent=""}
			<div class="error">
				{if $sSupport.sErrors.v}
					{foreach from=$sSupport.sErrors.v key=sKey item=sError}
						{if $sKey !=0&&$sSupport.sElements.$sError.error_msg}{$errorContent="{$errorContent}<br />"}{/if}
						{$errorContent="{$errorContent}{$sSupport.sElements.$sError.error_msg}"}
					{/foreach}
					{if $sSupport.sErrors.e}
						{$errorContent="{$errorContent}<br />"}
					{/if}
				{/if}
				{if $sSupport.sErrors.e}
					{$errorContent="{$errorContent}{s name='SupportInfoFillRedFields'}{/s}"}
				{/if}

				{include file="frontend/_includes/messages.tpl" type='error' content=$errorContent}
			</div>
		{/if}

		<div class="panel--body">
			{foreach from=$sSupport.sElements item=sElement key=sKey}
			{if $sSupport.sFields[$sKey]||$sElement.note}
				<div {if $sSupport.sElements[$sKey].typ eq 'textarea'}class="textarea"{elseif $sSupport.sElements[$sKey].typ eq 'checkbox'}class="checkbox"{elseif $sSupport.sElements[$sKey].typ eq 'select'}class="field--select"{/if}>
					{eval var=$sSupport.sFields[$sKey]}
				</div>

				{if $sElement.note}
					<p class="forms--description">
						{eval var=$sElement.note}
					</p>
				{/if}
			{/if}
			{/foreach}


			{block name='frontend_forms_elements_form_captcha'}
				{* Captcha *}
				<div class="forms--captcha">
					<div class="captcha--placeholder" data-src="{url module=widgets controller=Captcha action=refreshCaptcha}"></div>
					<strong class="captcha--notice">{s name='SupportLabelCaptcha'}{/s}</strong>
					<div class="captcha--code">
						<input type="text" required="required" aria-required="true" name="sCaptcha"{if $sSupport.sErrors.e.sCaptcha} class="has--error"{/if} />
					</div>
				</div>
			{/block}

			{block name='frontend_forms_elements_form_required'}
				{* Required fields hint *}
				<div class="forms--required">{s name='SupportLabelInfoFields'}{/s}</div>
			{/block}

			{block name='frontend_forms_elements_form_submit'}
				{* Forms Submit *}
				<div class="buttons">
					<button class="btn btn--primary" type="submit" name="Submit">{s name='SupportActionSubmit'}{/s}<i class="icon--arrow-right"></i></button>
				</div>
			{/block}
		</div>
</form>