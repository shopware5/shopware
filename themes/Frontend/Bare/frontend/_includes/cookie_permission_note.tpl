<div class="page-wrap--cookie-permission is--hidden"
     data-cookie-permission="true"
     data-basePath="{$Shop->getBaseUrl()}"
     data-shopId="{$Shop->getId()}">

    {block name="cookie_permission_container"}
        <div class="cookie-permission--container">
            {block name="cookie_permission_content"}
                <div class="cookie-permission--content">
                    {block name="cookie_permission_content_text"}
                        {s namespace="frontend/cookiepermission/index" name="cookiePermission/text"}{/s}
                    {/block}

                    {block name="cookie_permission_content_link"}
                        {$privacyLink = {config name="data_privacy_statement_link"}}
                        {if $privacyLink}
                            <a title="{s namespace="frontend/cookiepermission/index" name="cookiePermission/linkText"}{/s}"
                               href="{$privacyLink}">
                                {s namespace="frontend/cookiepermission/index" name="cookiePermission/linkText"}{/s}
                            </a>
                        {/if}
                    {/block}
                </div>
            {/block}

            {block name="cookie_permission_accept_button"}
                <div class="cookie-permission--button">
                    <a href="#" class="cookie-permission--accept-button btn is--primary is--large is--center">
                        {s namespace="frontend/cookiepermission/index" name="cookiePermission/buttonText"}{/s}
                    </a>
                </div>
            {/block}
        </div>
    {/block}
</div>