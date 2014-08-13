{extends file="frontend/index/index.tpl"}

{* Title *}
{block name='frontend_index_header_title'}
	{s name="RegisterTitle"}{/s} | {config name=shopName}
{/block}

{* Back to the shop button *}
{block name='frontend_index_logo_trusted_shops' append}
    {if $theme.checkoutHeader && $sTarget == "checkout"}
        <a href="{url controller='index'}"
           class="btn btn--grey is--small btn--back-top-shop"
           title="{s name='FinishButtonBackToShop' namespace='frontend/checkout/finish'}{/s}">
            <i class="icon--arrow-left is--small"></i>
            {s name="FinishButtonBackToShop" namespace="frontend/checkout/finish"}{/s}
        </a>
    {/if}
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}
    {if $sTarget != "checkout"}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide shop navigation *}
{block name='frontend_index_shop_navigation'}
    {if !$theme.checkoutHeader || $sTarget != "checkout"}
        {$smarty.block.parent}
    {/if}
{/block}

{* Step box *}
{block name='frontend_index_navigation_categories_top'}
    {if $theme.checkoutHeader && $sTarget == "checkout"}
        {include file="frontend/register/steps.tpl" sStepActive="address"}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide top bar *}
{block name='frontend_index_top_bar_container'}
    {if !$theme.checkoutHeader || $sTarget != "checkout"}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_index_logo_supportinfo"}
    {if $sTarget == "checkout"}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Hide footer *}
{block name="frontend_index_footer"}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Register content *}
{block name='frontend_index_content'}
	<div class="register--content panel content block has--border{if $register->personal->error_flags || $register->billing->error_flags || $register->shipping->error_flags} is--collapsed{/if}"
		 id="registration"
		 data-register="true">

		{block name='frontend_register_index_dealer_register'}
			{* Included for compatibility reasons *}
		{/block}

		{block name='frontend_register_index_cgroup_header'}
			{if $register.personal.form_data.sValidation}
				{* Include information related to registration for other customergroups then guest, this block get overridden by b2b essentials plugin *}
				<div class="panel register--supplier">
					<h2 class="panel--title is--underline">{$sShopname} {s name='RegisterHeadlineSupplier' namespace='frontend/register/index'}{/s}</h2>

					<div class="panel--body is--wide">
						<p class="is--bold">{s name='RegisterInfoSupplier3' namespace='frontend/register/index'}{/s}</p>

						<h3 class="is--bold">{s name='RegisterInfoSupplier4' namespace='frontend/register/index'}{/s}</h3>
						<p>{s name='RegisterInfoSupplier5' namespace='frontend/register/index'}{/s}</p>

						<h3 class="is--bold">{s name='RegisterInfoSupplier6' namespace='frontend/register/index'}{/s}</h3>
						<p>{s name='RegisterInfoSupplier7' namespace='frontend/register/index'}{/s}</p>
					</div>
				</div>
			{/if}
		{/block}

		{block name='frontend_register_index_form'}
			<form method="post" action="{url action=saveRegister sTarget=$sTarget sTargetAction=$sTargetAction}" class="panel register--form">

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

				{* Privacy checkbox *}
				{if !$update}
					{if {config name=ACTDPRCHECK}}
						{block name='frontend_register_index_input_privacy'}
							<div class="register--privacy">
								<input name="register[personal][dpacheckbox]" type="checkbox" id="dpacheckbox"{if $form_data.dpacheckbox} checked="checked"{/if} required="required" aria-required="true" value="1" class="chkbox is--required" />
								<label for="dpacheckbox" class="chklabel{if $register->personal->error_flags.dpacheckbox} has--error{/if}">{s name='RegisterLabelDataCheckbox'}{/s}</label>
							</div>
						{/block}
					{/if}
				{/if}

				{block name='frontend_register_index_form_required'}
					{* Required fields hint *}
					<div class="register--required-info required_fields">
						{s name='RegisterPersonalRequiredText' namespace='frontend/register/personal_fieldset'}{/s}
					</div>
				{/block}

				{block name='frontend_register_index_form_submit'}
					{* Submit button *}
					<div class="register--action">
						<input type="submit" class="register--submit btn btn--primary" value="{s name='RegisterIndexNewActionSubmit'}{/s}" />
					</div>
				{/block}
			</form>
		{/block}
	</div>

	{* Register Login *}
	{block name='frontend_register_index_login'}
		{include file="frontend/register/login.tpl"}
	{/block}

	{* Register advantages *}
	{block name='frontend_register_index_advantages'}
		<div class="register--advantages block">
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
		</div>
	{/block}

{/block}




