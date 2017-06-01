{extends file="frontend/account/sidebar.tpl"}

{block name="frontend_account_menu_title"}
    {if $userInfo}
        <h2 class="navigation--headline">
            {block name="frontend_account_menu_greeting"}
                {s name="AccountGreetingBefore"}{/s}
                {$userInfo['firstname']}
                {s name="AccountGreetingAfter"}{/s}
            {/block}
        </h2>
    {/if}
{/block}

{block name="frontend_account_menu_link_overview"}
    {if !$userInfo}
        <li class="navigation--entry">
            <span class="navigation--signin">
                <a href="{url module='frontend' controller='account'}#hide-registration"
                   class="blocked--link btn is--primary navigation--signin-btn{if $register} registration--menu-entry entry--close-off-canvas{/if}"
                   data-collapseTarget="#registration"
                   data-action="close">
                    {s name="AccountLogin"}{/s}
                </a>
                <span class="navigation--register">
                    {s name="AccountOr"}{/s}
                    <a href="{url module='frontend' controller='account'}#show-registration"
                       class="blocked--link{if $register} registration--menu-entry entry--close-off-canvas{/if}"
                       data-collapseTarget="#registration"
                       data-action="open">
                        {s name="AccountRegister"}{/s}
                    </a>
                </span>
            </span>
        </li>
    {/if}
    {$smarty.block.parent}
{/block}

{block name="frontend_account_menu_link_logout"}
    {if $userInfo}
        <li class="navigation--entry">
            <a href="{url controller='account' action='logout'}" title="{s name="AccountLogout"}{/s}"
               class="navigation--link link--logout navigation--personalized">
                {block name="frontend_account_menu_logout_personalized"}
                    <span class="navigation--logout-personalized blocked--link">
                        <i class="icon--logout blocked--link"></i>
                        {s name="AccountNot"}{/s}
                        {$userInfo['firstname']|truncate:15:"..."}?
                    </span>
                    <span class="navigation--logout blocked--link">{s name="AccountLogout"}{/s}</span>
                {/block}
            </a>
        </li>
    {/if}
{/block}