{extends file="frontend/account/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
	{$sBreadcrumb[] = ["name"=>"{s name="ChangeShippingTitle"}{/s}", "link"=>{url}]}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="account--change-shipping account--content register--content" data-register="true">

		{* Shipping headline *}
		{block name="frontend_account_shipping_headline"}
			<div class="account--welcome">
				<h1 class="panel--title">{s name="ShippingHeadline"}{/s}</h1>
			</div>
		{/block}

		{block name="frontend_account_shipping_content"}
			<div class="panel has--border is--rounded">

				{* Error messages *}
				{block name="frontend_account_shipping_error_messages"}
					{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
				{/block}

				{* Shipping form *}
				{block name="frontend_account_shipping_form"}
					<div class="account--shipping-form">
						<form name="frmRegister" method="post" action="{url controller=account action=saveShipping sTarget=$sTarget}">

                            {block name="frontend_account_shipping_hidden"}
                                <div class="register--alt-shipping is--hidden">
                                    <input type="checkbox" value="1" checked="checked" />
                                </div>
                            {/block}

							{* Shipping fieldset *}
							{block name="frontend_account_shipping_fieldset"}
								{include file="frontend/register/shipping_fieldset.tpl" form_data=$sFormData error_flags=$sErrorFlag country_list=$sCountryList}
							{/block}

                            {block name='frontend_account_shipping_required'}
                                {* Required fields hint *}
                                <div class="register--required-info required_fields">
                                    {s name='RegisterPersonalRequiredText' namespace='frontend/register/personal_fieldset'}{/s}
                                </div>
                            {/block}

							{* Shipping actions *}
							{block name="frontend_account_shipping_action_buttons"}
								<div class="account--actions">
                                    {block name="frontend_account_shipping_action_button_back"}
                                        {if $sTarget}
                                            <a class="btn is--secondary left" href="{url controller=$sTarget}" title="{"{s name="ShippingLinkBack"}{/s}"|escape}">
                                                {s name="ShippingLinkBack"}{/s}
                                            </a>
                                        {/if}
                                    {/block}
                                    {block name="frontend_account_shipping_action_button_send"}
									    <input type="submit" value="{s name="ShippingLinkSend"}{/s}" class="btn is--primary register--submit right" />
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