{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name='frontend_index_start'}
    {$smarty.block.parent}
    {s name="NewsletterTitle" assign="snippetNewsletterTitle"}{/s}
    {$sBreadcrumb = [['name' => $snippetNewsletterTitle, 'link' => {url}]]}
{/block}

{* Meta description *}
{block name='frontend_index_header_meta_description'}{s name='NewsletterMetaDescriptionStandard'}{/s}{/block}

{* Meta opengraph tags *}
{block name='frontend_index_header_meta_tags_opengraph'}
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{config name=sShopname}|escapeHtml}" />
    <meta property="og:title" content="{{config name=sShopname}|escapeHtml}" />
    <meta property="og:description" content="{s name='NewsletterMetaDescriptionStandard'}{/s}" />
    <meta property="og:image" content="{link file=$theme.desktopLogo fullPath}" />

    <meta name="twitter:card" content="website" />
    <meta name="twitter:site" content="{{config name=sShopname}|escapeHtml}" />
    <meta name="twitter:title" content="{{config name=sShopname}|escapeHtml}" />
    <meta name="twitter:description" content="{s name='NewsletterMetaDescriptionStandard'}{/s}" />
    <meta name="twitter:image" content="{link file=$theme.desktopLogo fullPath}" />
{/block}

{block name="frontend_index_content"}
    <div class="newsletter--content content block">

        {* Error messages *}
        {block name="frontend_newsletter_error_messages"}

            {if $sStatus.code != 0}
                <div class="newsletter--error-messages">

                    {$file = 'frontend/_includes/messages.tpl'}

                    {if $sStatus.code === 7}
                        {$type = 'error'}
                        {$content = "{s namespace="widgets/captcha/custom_captcha" name="invalidCaptchaMessage"}{/s}"}
                    {elseif $sStatus.code == 3}
                        {$type = 'success'}
                        {$content = $sStatus.message}
                    {elseif $sStatus.code == 5}
                        {$type = 'error'}
                        {$content = $sStatus.message}
                    {elseif $sStatus.code == 2}
                        {$type = 'warning'}
                        {$content = $sStatus.message}
                    {elseif $sStatus.code != 0}
                        {$type = 'error'}
                        {$content = $sStatus.message}
                    {/if}

                    {include file=$file type=$type content=$content}
                </div>
            {/if}
        {/block}

        {* Newsletter headline *}
        {block name="frontend_newsletter_headline"}
            <div class="newsletter--headline panel--body is--wide has--border is--rounded">
                {block name="frontend_newsletter_headline_title"}
                    <h1 class="newsletter--title">{s name="NewsletterRegisterHeadline"}{/s}</h1>
                {/block}

                {block name="frontend_newsletter_headline_info"}
                    <p class="newsletter--info">{s name="sNewsletterInfo"}{/s}</p>
                {/block}
            </div>
        {/block}

        {* Newsletter content *}
        {block name="frontend_newsletter_content"}
            {if $voteConfirmed == false || $sStatus.code == 0}
            <div class="newsletter--form panel has--border is--rounded" data-newsletter="true">

                {* Newsletter headline *}
                {block name="frontend_newsletter_content_headline"}
                    <h2 class="panel--title is--underline">{s name="NewsletterRegisterHeadline"}{/s}</h2>
                {/block}

                {* Newsletter form *}
                {block name="frontend_newsletter_form"}
                    <form action="{url controller='newsletter'}" method="post">
                        <div class="panel--body is--wide">

                            {* Subscription option *}
                            {block name="frontend_newsletter_form_input_subscription"}
                                <div class="newsletter--subscription select-field">
                                    <select name="subscribeToNewsletter" required="required" class="field--select newsletter--checkmail">
                                        <option value="1">{s name="sNewsletterOptionSubscribe"}{/s}</option>
                                        <option value="-1"{if $smarty.post.subscribeToNewsletter eq -1 || (!$smarty.post.subscribeToNewsletter && $sUnsubscribe == true)} selected="selected"{/if}>{s name="sNewsletterOptionUnsubscribe"}{/s}</option>
                                    </select>
                                </div>
                            {/block}

                            {* Email *}
                            {block name="frontend_newsletter_form_input_email"}
                                <div class="newsletter--email">
                                    <input name="newsletter" type="email" placeholder="{s name="sNewsletterPlaceholderMail"}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}" required="required" aria-required="true" value="{if $smarty.post.newsletter}{$smarty.post.newsletter|escape}{elseif $smarty.get.sNewsletter}{$smarty.get.sNewsletter|escape}{/if}" class="input--field is--required{if $sStatus.sErrorFlag.newsletter} has--error{/if}"/>
                                </div>
                            {/block}

                            {* Additional fields *}
                            {block name="frontend_newsletter_form_additionalfields"}
                                {if {config name=NewsletterExtendedFields}}
                                    <div class="newsletter--additional-form">

                                        {getSalutations variable="salutations"}

                                        {* Salutation *}
                                        {block name="frontend_newsletter_form_input_salutation"}
                                            <div class="newsletter--salutation select-field">
                                                <select name="salutation" class="field--select">
                                                    <option value=""{if $smarty.post.salutation eq ""} selected="selected"{/if}>{s name='NewsletterRegisterPlaceholderSalutation'}{/s}</option>
                                                    {foreach $salutations as $key => $label}
                                                        <option value="{$key}"{if $smarty.post.salutation eq $key} selected="selected"{/if}>{$label}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        {/block}

                                        {* Firstname *}
                                        {block name="frontend_newsletter_form_input_firstname"}
                                            <div class="newsletter--firstname">
                                                <input name="firstname" type="text" placeholder="{s name="NewsletterRegisterPlaceholderFirstname"}{/s}" value="{$smarty.post.firstname|escape}" class="input--field{if $sStatus.sErrorFlag.firstname} has--error{/if}"/>
                                            </div>
                                        {/block}

                                        {* Lastname *}
                                        {block name="frontend_newsletter_form_input_lastname"}
                                            <div class="newsletter--lastname">
                                                <input name="lastname" type="text" placeholder="{s name="NewsletterRegisterPlaceholderLastname"}{/s}" value="{$smarty.post.lastname|escape}" class="input--field{if $sStatus.sErrorFlag.lastname} has--error{/if}"/>
                                            </div>
                                        {/block}

                                        {* Street *}
                                        {block name="frontend_newsletter_form_input_street"}
                                            <div class="newsletter--street">
                                                <input name="street" type="text" placeholder="{s name="NewsletterRegisterBillingPlaceholderStreet"}{/s}" value="{$smarty.post.street|escape}" class="input--field input--field-street{if $sStatus.sErrorFlag.street} has--error{/if}"/>
                                            </div>
                                        {/block}

                                        {* Zip + City *}
                                        {block name="frontend_newsletter_form_input_zip_and_city"}
                                            <div class="newsletter--zip-city">
                                                {if {config name=showZipBeforeCity}}
                                                    <input name="zipcode" type="text" placeholder="{s name="NewsletterRegisterBillingPlaceholderZipcode"}{/s}" value="{$smarty.post.zipcode|escape}" class="input--field input--field-zipcode input--spacer{if $sStatus.sErrorFlag.zipcode} has--error{/if}"/>
                                                    <input name="city" type="text" placeholder="{s name="NewsletterRegisterBillingPlaceholderCityname"}{/s}" value="{$smarty.post.city|escape}" size="25" class="input--field input--field-city{if $sStatus.sErrorFlag.city} has--error{/if}"/>
                                                {else}
                                                    <input name="city" type="text" placeholder="{s name="NewsletterRegisterBillingPlaceholderCityname"}{/s}" value="{$smarty.post.city|escape}" size="25" class="input--field input--field-city input--spacer{if $sStatus.sErrorFlag.city} has--error{/if}"/>
                                                    <input name="zipcode" type="text" placeholder="{s name="NewsletterRegisterBillingPlaceholderZipcode"}{/s}" value="{$smarty.post.zipcode|escape}" class="input--field input--field-zipcode{if $sStatus.sErrorFlag.zipcode} has--error{/if}"/>
                                                {/if}
                                            </div>
                                        {/block}

                                    </div>

                                {/if}
                            {/block}

                            {* Required fields hint *}
                            {block name="frontend_newsletter_form_required"}
                                <div class="newsletter--required-info">
                                    {s name='RegisterPersonalRequiredText' namespace="frontend/register/personal_fieldset"}{/s}
                                </div>
                            {/block}

                            {* Captcha *}
                            {block name="frontend_newsletter_form_captcha"}
                                {if !({config name=noCaptchaAfterLogin} && $sUserLoggedIn)}
                                    {$newsletterCaptchaName = {config name=newsletterCaptcha}}
                                    <div class="newsletter--captcha-form">
                                        {if $newsletterCaptchaName === 'legacy'}
                                            <div class="newsletter--captcha">

                                                {* Deferred loading of the captcha image *}
                                                {block name='frontend_newsletter_form_captcha_placeholder'}
                                                    <div class="captcha--placeholder" {if $sErrorFlag.sCaptcha}
                                                         data-hasError="true"{/if}
                                                         data-src="{url module=widgets controller=Captcha action=refreshCaptcha}"
                                                         data-autoload="true">
                                                    </div>
                                                {/block}

                                                {block name='frontend_newsletter_form_captcha_label'}
                                                    <strong class="captcha--notice">{s name="SupportLabelCaptcha" namespace="frontend/forms/elements"}{/s}</strong>
                                                {/block}

                                                {block name='frontend_newsletter_form_captcha_code'}
                                                    <div class="captcha--code">
                                                        <input type="text" name="sCaptcha" class="newsletter--field{if $sErrorFlag.sCaptcha} has--error{/if}" required="required" aria-required="true" />
                                                    </div>
                                                {/block}
                                            </div>
                                        {else}
                                            {$captchaName = $newsletterCaptchaName}
                                            {$captchaHasError = isset($sErrorFlag) && count($sErrorFlag) > 0}
                                            {include file="widgets/captcha/custom_captcha.tpl" captchaName=$captchaName captchaHasError=$captchaHasError}
                                        {/if}
                                    </div>
                                {/if}
                            {/block}

                            {* Data protection information *}
                            {block name="frontend_newsletter_form_privacy"}
                                {if {config name=ACTDPRTEXT} || {config name=ACTDPRCHECK}}
                                    {include file="frontend/_includes/privacy.tpl"}
                                {/if}
                            {/block}

                            {* Submit button *}
                            {block name="frontend_newsletter_form_submit"}
                                <div class="newsletter--action">
                                    <button type="submit" class="btn is--primary right is--icon-right" name="{s name="sNewsletterButton"}{/s}">
                                        {s name="sNewsletterButton"}{/s}
                                        <i class="icon--arrow-right"></i>
                                    </button>
                                </div>
                            {/block}
                        </div>
                    </form>
                {/block}
            </div>
            {/if}
        {/block}
    </div>
{/block}
