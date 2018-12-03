{block name="frontend_widgets_captcha_custom_captcha"}

    {block name="frontend_widgets_captcha_custom_captcha_config"}
        {$isHoneypot = $captchaName === 'honeypot'}
        {$isNoCaptcha = $captchaName === 'nocaptcha'}
        {$hasError = isset($captchaHasError) && count($captchaHasError) > 0}
    {/block}

    {if !$isNoCaptcha}
        <div class="{if !$isHoneypot }panel--body is--wide{/if}">
            {block name="frontend_widgets_captcha_custom_captcha_placeholder"}
                <div class="captcha--placeholder"
                    data-captcha="true"
                    data-src="{url module=widgets controller=Captcha action=getCaptchaByName captchaName=$captchaName}"
                    data-errorMessage="{s name="invalidCaptchaMessage" namespace="widgets/captcha/custom_captcha"}{/s}"
                    {if $hasError}data-hasError="true"{/if}>

                    {block name="frontend_widgets_captcha_custom_captcha_honeypot"}
                        {if $isHoneypot}
                            {include file="widgets/captcha/honeypot.tpl"}
                        {/if}
                    {/block}
                </div>
            {/block}

            {block name="frontend_widgets_captcha_custom_captcha_hidden_input"}
                <input type="hidden" name="captchaName" value="{$captchaName}" />
            {/block}
        </div>
    {/if}

{/block}