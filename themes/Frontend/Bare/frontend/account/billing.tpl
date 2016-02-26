{extends file="frontend/account/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
	{$sBreadcrumb[] = ["name"=>"{s name="ChangeBillingTitle"}{/s}", "link"=>{url}]}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="account--change-billing account--content register--content" data-register="true">

		{* Billing headline *}
		{block name="frontend_account_billing_headline"}
			<div class="account--welcome">
				<h1 class="panel--title">{s name='BillingHeadline'}{/s}</h1>
			</div>
		{/block}

		{block name="frontend_account_billing_content"}
			<div class="panel has--border is--rounded">

				{* Error messages *}
				{block name="frontend_account_error_messages"}
					{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
				{/block}

				{* Personal form *}
				{block name="frontend_account_billing_form"}
					<div class="account--billing-form">
						<form name="frmRegister" method="post" action="{url controller=account action=saveBilling sTarget=$sTarget}">

							{* Personal fieldset *}
							{block name="frontend_account_personal_information"}
								{include file="frontend/register/personal_fieldset.tpl" fieldset_title="{s name='RegisterPersonalBaseDataHeadline' namespace='frontend/register/personal_fieldset'}{/s}" update=true form_data=$sFormData error_flags=$sErrorFlag}
							{/block}

							{* Billing fieldset *}
							{block name="frontend_account_billing_information"}
								{include file="frontend/register/billing_fieldset.tpl" update=true form_data=$sFormData error_flags=$sErrorFlag country_list=$sCountryList}
							{/block}

                            {block name='frontend_account_billing_required'}
                                {* Required fields hint *}
                                <div class="register--required-info required_fields">
                                    {s name='RegisterPersonalRequiredText' namespace='frontend/register/personal_fieldset'}{/s}
                                </div>
                            {/block}

							{* Billing actions *}
							{block name="frontend_account_billing_action_buttons"}
								<div class="account--actions">
                                    {block name="frontend_account_billing_action_button_back"}
                                        {if $sTarget}
                                            <a class="btn is--secondary left" href="{url controller=$sTarget}" title="{"{s name='BillingLinkBack'}{/s}"|escape}">
                                                {s name="BillingLinkBack"}{/s}
                                            </a>
                                        {/if}
                                    {/block}
                                    {block name="frontend_account_billing_action_button_send"}
									    <input type="submit" value="{s name='BillingLinkSend'}{/s}" class="btn is--primary register--submit right"/>
                                    {/block}
								</div>
							{/block}

						</form>
					</div>
				{/block}

			</div>
		{/block}

	</div>
{/block}