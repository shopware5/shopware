{extends file="frontend/index/index.tpl"}

{* Title *}
{block name='frontend_index_header_title'}
	{s name="RegisterTitle"}{/s} | {config name=shopName}
{/block}

{* Step box *}
{block name="frontend_index_content_top"}
	{include file="frontend/register/steps.tpl" sStepActive="register"}
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}<div class="clear"></div>{/block}

{block name="frontend_index_content"}
	<div class="grid_16 register" id="center">

		{block name='frontend_register_index_dealer_register'}
			{* Included for compatibility reasons *}
		{/block}

		{block name='frontend_register_index_cgroup_header'}
			{if $register.personal.form_data.sValidation}
			{* Include information related to registration for other customergroups then guest, this block get overridden by b2b essentials plugin *}
				<div class="supplier_register">
					<div class="inner_container">
							<h1>{$sShopname} {s name='RegisterHeadlineSupplier' namespace='frontend/register/index'}{/s}</h1>
							<strong>{s name='RegisterInfoSupplier' namespace='frontend/register/index'}{/s}</strong><br />
							<a href="{url controller='account'}" class="account">{s name='RegisterInfoSupplier2' namespace='frontend/register/index'}{/s}</a>

							<div class="space">&nbsp;</div>

							<h4 class="bold">{s name='RegisterInfoSupplier3' namespace='frontend/register/index'}{/s}</h4>

							<h5 class="bold">{s name='RegisterInfoSupplier4' namespace='frontend/register/index'}{/s}</h5>{s name='RegisterInfoSupplier5' namespace='frontend/register/index'}{/s}
							<div class="space">&nbsp;</div>

						   <h5 class="bold">{s name='RegisterInfoSupplier6' namespace='frontend/register/index'}{/s}</h5>{s name='RegisterInfoSupplier7' namespace='frontend/register/index'}{/s}
					</div>
				</div>
			{/if}
		{/block}

			
		<form method="post" action="{url action=saveRegister}">
			
			{include file="frontend/register/error_message.tpl" error_messages=$register->personal->error_messages}
			{include file="frontend/register/personal_fieldset.tpl" form_data=$register->personal->form_data error_flags=$register->personal->error_flags}
			
			{include file="frontend/register/error_message.tpl" error_messages=$register->billing->error_messages}
			{include file="frontend/register/billing_fieldset.tpl" form_data=$register->billing->form_data error_flags=$register->billing->error_flags country_list=$register->billing->country_list}
			
			{include file="frontend/register/error_message.tpl" error_messages=$register->shipping->error_messages}
			{include file="frontend/register/shipping_fieldset.tpl" form_data=$register->shipping->form_data error_flags=$register->shipping->error_flags country_list=$register->shipping->country_list}
			
			<div class="payment_method register_last"></div>

			{* Privacy checkbox *}
			{if !$update}
				{if {config name=ACTDPRCHECK}}
					{block name='frontend_register_index_input_privacy'}
						<div class="privacy">
							<input name="register[personal][dpacheckbox]" type="checkbox" id="dpacheckbox"{if $form_data.dpacheckbox} checked="checked"{/if} value="1" class="chkbox" />
							<label for="dpacheckbox" class="chklabel{if $register->personal->error_flags.dpacheckbox} instyle_error{/if}">{s name='RegisterLabelDataCheckbox'}{/s}</label>
							<div class="clear">&nbsp;</div>
						</div>
					{/block}
				{/if}
			{/if}
			
			{* Required fields hint *}
			<div class="required_fields">
				{s name='RegisterPersonalRequiredText' namespace='frontend/register/personal_fieldset'}{/s}
			</div>
			
			<div class="actions">
				<input id="registerbutton" type="submit" class="right" value="{s name='RegisterIndexActionSubmit'}{/s}" />
				<hr class="clear space" />
			</div>
			
		</form>
	</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
	<div id="right" class="grid_5 register last">
		<div class="register_info">
			{s name='RegisterInfoAdvantages'}{/s}
		</div>
	</div>
{/block}