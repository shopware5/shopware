{block name="frontend_index_cookie_consent_manager_cookie"}
    <div class='cookie-consent--cookie'>
        {block name="frontend_index_cookie_consent_manager_cookie_name_input"}
            <input type="hidden" class="cookie-consent--cookie-name" value="{$cookie['name']}" />
        {/block}

        {block name="frontend_index_cookie_consent_manager_cookie_state_input"}
            <label class="cookie-consent--cookie-state cookie-consent--state-input{if $cookieGroup['required']} cookie-consent--required{/if}">
                <input type="checkbox" name="{$cookie['name']}-state" class="cookie-consent--cookie-state-input"{if $cookieGroup['required']} disabled="disabled" checked="checked"{/if} />
                <span class="cookie-consent--state-input-element"></span>
            </label>
        {/block}

        {block name="frontend_index_cookie_consent_manager_cookie_label"}
            <div class='cookie--label cookie-consent--state-label'>
                {$cookie['label']}
            </div>
        {/block}
    </div>
{/block}