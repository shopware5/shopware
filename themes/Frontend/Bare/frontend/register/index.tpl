{extends file="frontend/index/index.tpl"}

{block name="frontend_index_header"}
    {$toAccount = ($sTarget == "account" || $sTarget == "address")}
    {$smarty.block.parent}
{/block}

{* Title *}
{block name='frontend_index_header_title'}
    {s name="RegisterTitle"}{/s} | {{config name=shopName}|escapeHtml}
{/block}

{* Back to the shop button *}
{block name='frontend_index_logo_trusted_shops'}
    {$smarty.block.parent}
    {if $theme.checkoutHeader && !$toAccount}
        <a href="{url controller='index'}"
           class="btn is--small btn--back-top-shop is--icon-left"
           title="{"{s name='FinishButtonBackToShop' namespace='frontend/checkout/finish'}{/s}"|escape}">
            <i class="icon--arrow-left"></i>
            {s name="FinishButtonBackToShop" namespace="frontend/checkout/finish"}{/s}
        </a>
    {/if}
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}
    {if $toAccount}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide shop navigation *}
{block name='frontend_index_shop_navigation'}
    {if !$theme.checkoutHeader || $toAccount}
        {$smarty.block.parent}
    {/if}
{/block}

{* Step box *}
{block name='frontend_index_navigation_categories_top'}
    {if $toAccount}
        {$smarty.block.parent}
    {else}
        {if !$theme.checkoutHeader}
            {$smarty.block.parent}
        {/if}
        {include file="frontend/register/steps.tpl" sStepActive="address"}
    {/if}
{/block}

{* Hide top bar *}
{block name='frontend_index_top_bar_container'}
    {if !$theme.checkoutHeader || $toAccount}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_index_logo_supportinfo"}
    {if !$toAccount}
        {$smarty.block.parent}
    {/if}
{/block}

{* Sidebar left *}
{block name='frontend_index_content_left'}
    {include file='frontend/index/sidebar.tpl'}
{/block}

{* Footer *}
{block name="frontend_index_footer"}
    {if !$theme.checkoutFooter || $toAccount}
        {$smarty.block.parent}
    {else}
        {block name="frontend_index_register_footer"}
            {include file="frontend/index/footer_minimal.tpl"}
        {/block}
    {/if}
{/block}

{* Register content *}
{block name='frontend_index_content'}
    {block name='frontend_register_index_registration'}
        <div class="register--content panel content block has--border is--rounded{if $errors.occurred} is--collapsed{/if}" id="registration" data-register="true">

            {block name='frontend_register_index_dealer_register'}
                {* Included for compatibility reasons *}
            {/block}

            {block name='frontend_register_index_cgroup_header'}
                {if $register.personal.sValidation}
                    {* Include information related to registration for other customergroups then guest, this block get overridden by b2b essentials plugin *}
                    <div class="panel register--supplier">
                        {block name='frontend_register_index_cgroup_header_title'}
                            <h2 class="panel--title is--underline">{$sShopname|escapeHtml} {s name='RegisterHeadlineSupplier' namespace='frontend/register/index'}{/s}</h2>
                        {/block}

                        {block name='frontend_register_index_cgroup_header_body'}
                            <div class="panel--body is--wide">
                                <p class="is--bold">{s name='RegisterInfoSupplier3' namespace='frontend/register/index'}{/s}</p>

                                <h3 class="is--bold">{s name='RegisterInfoSupplier4' namespace='frontend/register/index'}{/s}</h3>
                                <p>{s name='RegisterInfoSupplier5' namespace='frontend/register/index'}{/s}</p>

                                <h3 class="is--bold">{s name='RegisterInfoSupplier6' namespace='frontend/register/index'}{/s}</h3>
                                <p>{s name='RegisterInfoSupplier7' namespace='frontend/register/index'}{/s}</p>
                            </div>
                        {/block}
                    </div>
                {/if}
            {/block}

            {block name='frontend_register_index_form'}
                <form method="post" action="{url action=saveRegister sTarget=$sTarget sTargetAction=$sTargetAction}" class="panel register--form">

                    {block name='frontend_register_index_form_captcha_fieldset'}
                        {include file="frontend/register/error_message.tpl" error_messages=$errors.captcha}
                    {/block}

                    {block name='frontend_register_index_form_personal_fieldset'}
                        {include file="frontend/register/error_message.tpl" error_messages=$errors.personal}
                        {include file="frontend/register/personal_fieldset.tpl" form_data=$register.personal error_flags=$errors.personal}
                    {/block}

                    {block name='frontend_register_index_form_billing_fieldset'}
                        {include file="frontend/register/error_message.tpl" error_messages=$errors.billing}
                        {include file="frontend/register/billing_fieldset.tpl" form_data=$register.billing error_flags=$errors.billing country_list=$countryList}
                    {/block}

                    {block name='frontend_register_index_form_shipping_fieldset'}
                        {include file="frontend/register/error_message.tpl" error_messages=$errors.shipping}
                        {include file="frontend/register/shipping_fieldset.tpl" form_data=$register.shipping error_flags=$errors.shipping country_list=$countryList}
                    {/block}

                    {block name='frontend_register_index_form_required'}
                        {* Required fields hint *}
                        <div class="register--required-info required_fields">
                            {s name='RegisterPersonalRequiredText' namespace='frontend/register/personal_fieldset'}{/s}
                        </div>
                    {/block}

                    {* Captcha *}
                    {block name='frontend_register_index_form_captcha'}
                        {$captchaHasError = $errors.captcha}
                        {$captchaName = {config name=registerCaptcha}}
                        {include file="widgets/captcha/custom_captcha.tpl" captchaName=$captchaName captchaHasError=$captchaHasError}
                    {/block}

                    {* Data protection information *}
                    {block name="frontend_register_index_form_privacy"}
                        {if {config name=ACTDPRTEXT}}
                            <h2 class="panel--title is--underline">
                                {s name="PrivacyTitle" namespace="frontend/index/privacy"}{/s}
                            </h2>
                            <div class="panel--body is--wide">
                                <div class="register--password-description">
                                    {* Privacy checkbox *}
                                    {if !$update}
                                        {if {config name=ACTDPRCHECK}}
                                            <input name="register[personal][dpacheckbox]" type="checkbox" id="dpacheckbox"{if $form_data.dpacheckbox} checked="checked"{/if} required="required" aria-required="true" value="1" class="is--required" />
                                            <label for="privacy-text">
                                        {/if}
                                    {/if}
                                    {s name="PrivacyText" namespace="frontend/index/privacy"}{/s}
                                    {if {config name=ACTDPRCHECK}}
                                        </label>
                                    {/if}
                                </div>
                            </div>
                        {/if}
                    {/block}

                    {block name='frontend_register_index_form_submit'}
                        {* Submit button *}
                        <div class="register--action">
                            <button type="submit" class="register--submit btn is--primary is--large is--icon-right" name="Submit">{s name="RegisterIndexNewActionSubmit"}{/s} <i class="icon--arrow-right"></i></button>
                        </div>
                    {/block}
                </form>
            {/block}
        </div>
    {/block}

    {* Register Login *}
    {block name='frontend_register_index_login'}
        {include file="frontend/register/login.tpl"}
    {/block}

    {* Register advantages *}
    {block name='frontend_register_index_advantages'}
        <div class="register--advantages block">
            {block name='frontend_register_index_advantages_title'}
                <h2 class="panel--title">{s name='RegisterInfoAdvantagesTitle'}{/s}</h2>
            {/block}
            {block name='frontend_index_content_advantages_list'}
                <ul class="list--unordered is--checked register--advantages-list">
                    {block name='frontend_index_content_advantages_entry1'}
                        <li class="register--advantages-entry">
                            {s name='RegisterInfoAdvantagesEntry1'}{/s}
                        </li>
                    {/block}

                    {block name='frontend_index_content_advantages_entry2'}
                        <li class="register--advantages-entry">
                            {s name='RegisterInfoAdvantagesEntry2'}{/s}
                        </li>
                    {/block}

                    {block name='frontend_index_content_advantages_entry3'}
                        <li class="register--advantages-entry">
                            {s name='RegisterInfoAdvantagesEntry3'}{/s}
                        </li>
                    {/block}

                    {block name='frontend_index_content_advantages_entry4'}
                        <li class="register--advantages-entry">
                            {s name='RegisterInfoAdvantagesEntry4'}{/s}
                        </li>
                    {/block}
                </ul>
            {/block}
        </div>
    {/block}

{/block}
