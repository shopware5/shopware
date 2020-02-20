{namespace name='frontend/cookie_consent/main'}

<div id='cookie-consent' class='off-canvas is--left block-transition' data-cookie-consent-manager='true'>
    {block name='frontend_index_cookie_consent_manager_content'}
        {block name='frontend_index_cookie_consent_manager_header'}
            <div class='cookie-consent--header cookie-consent--close'>
                {s name="manager/title"}{/s}

                <i class="icon--arrow-right"></i>
           </div>
        {/block}

        {block name='frontend_index_cookie_consent_manager_description'}
            <div class='cookie-consent--description'>
                {s name="manager/description"}{/s}
            </div>
        {/block}

        {if $cookieGroups}
            {block name='frontend_index_cookie_consent_manager_configuration'}
                    <div class='cookie-consent--configuration'>
                        {block name="frontend_index_cookie_consent_manager_configuration_header"}
                            <div class='cookie-consent--configuration-header'>
                                <div class='cookie-consent--configuration-header-text'>{s name="manager/configuration/title"}{/s}</div>
                            </div>
                        {/block}

                        {block name="frontend_index_cookie_consent_manager_configuration_container"}
                            <div class='cookie-consent--configuration-main'>
                                {foreach $cookieGroups as $cookieGroup}
                                    {if $cookieGroup['cookies']|count}
                                       {include file="frontend/index/cookie_consent/group.tpl"}
                                    {/if}
                                {/foreach}
                            </div>
                        {/block}
                    </div>
            {/block}

            {block name="frontend_index_cookie_consent_manager_save"}
                <div class="cookie-consent--save">
                    <input class="cookie-consent--save-button btn is--primary" type="button" value="{s name="manager/save"}{/s}" />
                </div>
            {/block}
        {/if}
    {/block}
</div>
