{extends file="frontend/index/index.tpl"}

{* Title *}
{block name='frontend_index_header_title'}
	{s name="RegisterTitle"}{/s} | {config name=shopName}
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}{/block}

{* Hide shop navigation *}
{block name='frontend_index_shop_navigation'}{/block}

{* Hide top bar navigation *}
{block name='frontend_index_navigation'}
	<header class="header-main">
		<div class="container">

			{* Logo container *}
			{block name='frontend_index_logo_container'}
				{include file="frontend/index/logo-container.tpl"}

			{/block}

			{* Shop navigation *}
			{block name='frontend_index_shop_navigation'}
				{include file="frontend/index/shop-navigation.tpl"}
			{/block}
		</div>
	</header>

	{* Hide maincategories navigation top *}
	{block name='frontend_index_navigation_categories_top'}{/block}
{/block}

{* Step box *}
{block name="frontend_index_navigation_categories_top" append}
	{include file="frontend/register/steps.tpl" sStepActive="register"}
{/block}

{* Hide footer *}
{block name="frontend_index_footer"}{/block}


{block name="frontend_index_content"}
	<div class="register--content panel content block has--border">

		{block name='frontend_register_index_dealer_register'}
			{* Included for compatibility reasons *}
		{/block}

		{block name='frontend_register_index_cgroup_header'}
			{if $register.personal.form_data.sValidation}
			{* Include information related to registration for other customergroups then guest, this block get overridden by b2b essentials plugin *}
			<div class="panel register--supplier">
				<h2 class="panel--title is--underline">{$sShopname} {s name='RegisterHeadlineSupplier' namespace='frontend/register/index'}{/s}</h2>

				<div class="panel--body">

					<strong>{s name='RegisterInfoSupplier' namespace='frontend/register/index'}{/s}</strong><br />
					<a href="{url controller='account'}" class="account">{s name='RegisterInfoSupplier2' namespace='frontend/register/index'}{/s}</a><br />
					<p class="is--bold">{s name='RegisterInfoSupplier3' namespace='frontend/register/index'}{/s}</p>
					<h3 class="is--bold">{s name='RegisterInfoSupplier4' namespace='frontend/register/index'}{/s}</h3>{s name='RegisterInfoSupplier5' namespace='frontend/register/index'}{/s}
					<h3 class="is--bold">{s name='RegisterInfoSupplier6' namespace='frontend/register/index'}{/s}</h3>{s name='RegisterInfoSupplier7' namespace='frontend/register/index'}{/s}

				</div>
			</div>
			{/if}
		{/block}

		{block name='frontend_register_index_form'}
			<form method="post" action="{url action=saveRegister}" class="panel register--form">

				{block name='frontend_register_index_form_personal_fieldset'}
					{include file="frontend/register/error_message.tpl" error_messages=$register->personal->error_messages}
					{include file="frontend/register/personal_fieldset.tpl" form_data=$register->personal->form_data error_flags=$register->personal->error_flags}
				{/block}

				{block name='frontend_register_index_form_billing_fieldset'}
					{include file="frontend/register/error_message.tpl" error_messages=$register->billing->error_messages}
					{include file="frontend/register/billing_fieldset.tpl" form_data=$register->billing->form_data error_flags=$register->billing->error_flags country_list=$register->billing->country_list}
				{/block}

				{block name='frontend_register_index_form_shipping_fieldset'}
					{include file="frontend/register/error_message.tpl" error_messages=$register->shipping->error_messages}
					{include file="frontend/register/shipping_fieldset.tpl" form_data=$register->shipping->form_data error_flags=$register->shipping->error_flags country_list=$register->shipping->country_list}
				{/block}

				<div class="payment_method register_last"></div>

				{* Privacy checkbox *}
				{if !$update}
					{if {config name=ACTDPRCHECK}}
						{block name='frontend_register_index_input_privacy'}
							<div class="privacy">
								<input name="register[personal][dpacheckbox]" type="checkbox" id="dpacheckbox"{if $form_data.dpacheckbox} checked="checked"{/if} value="1" class="chkbox" />
								<label for="dpacheckbox" class="chklabel{if $register->personal->error_flags.dpacheckbox} instyle_error{/if}">{s name='RegisterLabelDataCheckbox'}{/s}</label>
							</div>
						{/block}
					{/if}
				{/if}

				{block name='frntend_register_index_form_regquired'}
					{* Required fields hint *}
					<div class="register--required-info required_fields">
						{s name='RegisterPersonalRequiredText' namespace='frontend/register/personal_fieldset'}{/s}
					</div>
				{/block}

				{block name='frontend_register_index_form_submit'}
					{* Submit button *}
					<div class="register--submit actions">
						<button type="submit" class="btn btn--primary">{s name='RegisterIndexActionSubmit'}{/s} <i class="icon--arrow-right"></i></button>
					</div>
				{/block}
			</form>
		{/block}
	</div>
{/block}

{* Register advantages *}
{block name='frontend_index_content_right'}
	<div class="panel register--advantages container">
		<div class="panel--body">
			{block name='frontend_index_content_advantages'}

				<h2 class="panel--title">{s name='RegisterInfoAdvantagesTitle'}{/s}</h2>

				{block name='frontend_index_content_advantages_list'}
					<ul class="register--advantages-list">
						{block name='frontend_index_content_advantages_entry1'}
							<li class="register--advantages-entry">
								<i class="icon--check"></i>
								{s name='RegisterInfoAdvantagesEntry1'}{/s}
							</li>
						{/block}

						{block name='frontend_index_content_advantages_entry2'}
							<li class="register--advantages-entry">
								<i class="icon--check"></i>
								{s name='RegisterInfoAdvantagesEntry2'}{/s}
							</li>
						{/block}

						{block name='frontend_index_content_advantages_entry3'}
							<li class="register--advantages-entry">
								<i class="icon--check"></i>
								{s name='RegisterInfoAdvantagesEntry3'}{/s}
							</li>
						{/block}

						{block name='frontend_index_content_advantages_entry4'}
							<li class="register--advantages-entry">
								<i class="icon--check"></i>
								{s name='RegisterInfoAdvantagesEntry4'}{/s}
							</li>
						{/block}
					</ul>
				{/block}

			{/block}
		</div>
	</div>
{/block}
