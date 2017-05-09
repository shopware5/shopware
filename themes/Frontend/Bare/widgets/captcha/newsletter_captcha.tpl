{block name="frontend_widgets_captcha_newsletter_captcha_placeholder"}
    <div class="captcha--placeholder"
         data-captcha="true"
         data-autoload="true"
         data-src="{url module=widgets controller=Captcha action=getCaptchaByName captchaName=$captchaName}"
            {if isset($captchaHasError) && count($captchaHasError) > 0}
        data-hasError="true"
            {/if}>
    </div>
{/block}

{block name="frontend_widgets_captcha_newsletter_captcha_hidden_input"}
    <input type="hidden" name="captchaName" value="{$captchaName}" />
{/block}