{block name="frontend_index_cookie_consent_manager_group"}
    <div class='cookie-consent--group'>
        {block name="frontend_index_cookie_consent_manager_group_name_input"}
            <input type="hidden" class="cookie-consent--group-name" value="{$cookieGroup['name']}" />
        {/block}

        {block name="frontend_index_cookie_consent_manager_group_state_input"}
            <label class="cookie-consent--group-state cookie-consent--state-input{if $cookieGroup['required']} cookie-consent--required{/if}">
                <input type="checkbox" name="{$cookieGroup['name']}-state" class="cookie-consent--group-state-input"{if $cookieGroup['required']} disabled="disabled" checked="checked"{/if}/>
                <span class="cookie-consent--state-input-element"></span>
            </label>
        {/block}

        {block name="frontend_index_cookie_consent_manager_group_title"}
            <div class='cookie-consent--group-title' data-collapse-panel='true' data-contentSiblingSelector=".cookie-consent--group-container">
                <div class="cookie-consent--group-title-label cookie-consent--state-label">
                    {$cookieGroup['label']|truncate:25}
                </div>

                <span class="cookie-consent--group-arrow is-icon--right">
                    <i class="icon--arrow-right"></i>
                </span>
            </div>
        {/block}

        {if $cookieGroup['description'] || $cookieGroup['cookies']}
            {block name="frontend_index_cookie_consent_manager_group_container"}
                <div class='cookie-consent--group-container'>
                    {if {$cookieGroup['description']}}
                        {block name="frontend_index_cookie_consent_manager_group_description"}
                            <div class='cookie-consent--group-description'>
                                {$cookieGroup['description']}
                            </div>
                        {/block}
                    {/if}

                    <div class='cookie-consent--cookies-container'>
                        {foreach $cookieGroup['cookies'] as $cookie}
                            {include file="frontend/index/cookie_consent/cookie.tpl"}
                        {/foreach}
                    </div>
                </div>
            {/block}
        {/if}
    </div>
{/block}
