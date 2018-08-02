{namespace name="frontend/cookiepermission/index"}
<div class="page-wrap--cookie-permission is--hidden"
     data-cookie-permission="true"
     data-urlPrefix="{url controller=index action=index}"
     data-title="{s name="cookiePermission/title"}{/s}"
     {if $Shop}data-shopId="{$Shop->getId()}"{/if}>

    {if {config name="cookie_note_mode"} != 2}
        {block name="cookie_permission_container"}
            <div class="cookie-permission--container cookie-mode--{config name="cookie_note_mode"}">
                {block name="cookie_permission_content"}
                    <div class="cookie-permission--content">
                        {block name="cookie_permission_content_text"}
                            {if {config name="cookie_note_mode"} == 1}
                                {s name="cookiePermission/textMode1"}{/s}
                            {else}
                                {s name="cookiePermission/text"}{/s}
                            {/if}
                        {/block}

                        {block name="cookie_permission_content_link"}
                            {$privacyLink = {config name="data_privacy_statement_link"}}
                            {if $privacyLink}
                                <a title="{s name="cookiePermission/linkText"}{/s}"
                                   class="cookie-permission--privacy-link"
                                   href="{$privacyLink}">
                                    {s name="cookiePermission/linkText"}{/s}
                                </a>
                            {/if}
                        {/block}
                    </div>
                {/block}

                {block name="cookie_permission_accept_button"}
                    <div class="cookie-permission--button">
                        {block name="cookie_permission_decline_button_fixed"}
                            {if {config name="cookie_note_mode"} == 1}
                                {block name="cookie_permission_decline_button"}
                                    <a href="#" class="cookie-permission--decline-button btn is--large is--center">
                                        {s name="cookiePermission/declineText"}{/s}
                                    </a>
                                {/block}
                            {/if}
                        {/block}

                        {block name="cookie_permission_accept_button_fixed"}
                            <a href="#" class="cookie-permission--accept-button btn is--primary is--large is--center">
                                {s name="cookiePermission/buttonText"}{/s}
                            </a>
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}
    {else}
        {block name="cookie_removal_container"}
            <div class="cookie-removal--container">
                <p>
                    {s name="cookiePermission/infoText"}{/s}<br>
                </p>
                <ul class="cookie-removal--list">
                    <li>{s name="cookiePermission/productToCart"}{/s}</li>
                    <li>{s name="cookiePermission/wishList"}{/s}</li>
                    <li>{s name="cookiePermission/productRecommandations"}{/s}</li>
                </ul>

                {block name="cookie_removal_container_footer"}
                    <div class="cookie-removal--footer">
                        {$privacyLink = {config name="data_privacy_statement_link"}}
                        {if $privacyLink}
                            <a title="{s name="cookiePermission/linkText"}{/s}"
                               class="privacy--notice"
                               href="{$privacyLink}">
                                {s name="cookiePermission/linkText"}{/s}
                            </a>
                        {/if}

                        <div class="cookie-removal--buttons">
                            <a class="btn is--secondary cookie-permission--accept-button is--center">{s name="cookiePermission/buttonText"}{/s}</a>
                            <a class="btn cookie-permission--close-button is--center">{s name="cookiePermission/close"}{/s}</a>
                        </div>
                    </div>
                {/block}
            </div>
        {/block}
    {/if}
</div>