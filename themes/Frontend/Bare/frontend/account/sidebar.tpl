{if ($sUserLoggedIn && !$sOneTimeAccount) || $showSidebar}
<div class="account--menu is--rounded">
    {block name="frontend_account_menu"}

        {* Sidebar navigation headline *}
        {block name="frontend_account_menu_title"}
            <h2 class="navigation--headline">
                {s name="AccountHeaderNavigation"}{/s}
            </h2>
        {/block}

        {* Sidebar menu container *}
        <div class="account--menu-container">

            {block name="frontend_account_menu_container"}
                {* Sidebar navigation *}
                <ul class="sidebar--navigation navigation--list is--level0 show--active-items">
                    {block name="frontend_account_menu_list"}
                        {* Link to the account overview page *}
                        {block name="frontend_account_menu_link_overview"}
                            <li class="navigation--entry">
                                <a href="{url module='frontend' controller='account'}" title="{s name="AccountLinkOverview"}{/s}" class="navigation--link{if {controllerName|lower} == 'account' && $sAction == 'index'} is--active{/if}">
                                    {s name="AccountLinkOverview"}{/s}
                                </a>
                            </li>
                        {/block}

                        {* Link to the account overview page *}
                        {block name="frontend_account_menu_link_profile"}
                            <li class="navigation--entry">
                                <a href="{url module='frontend' controller='account' action=profile}" title="{s name="AccountLinkProfile"}{/s}" class="navigation--link{if {controllerName|lower} == 'account' && $sAction == 'profile'} is--active{/if}">
                                    {s name="AccountLinkProfile"}{/s}
                                </a>
                            </li>
                        {/block}

                        {* Link to the user addresses *}
                        {block name="frontend_account_menu_link_addresses"}
                            <li class="navigation--entry">
                                <a href="{url module='frontend' controller='address' action='index'}" title="{s name="AccountLinkAddresses"}My addresses{/s}" class="navigation--link{if {controllerName} == 'address'} is--active{/if}">
                                    {s name="AccountLinkAddresses"}My addresses{/s}
                                </a>
                            </li>
                        {/block}

                        {* Link to the user payment method settings *}
                        {block name="frontend_account_menu_link_payment"}
                            <li class="navigation--entry">
                                <a href="{url module='frontend' controller='account' action='payment'}" title="{s name="AccountLinkPayment"}{/s}" class="navigation--link{if $sAction == 'payment'} is--active{/if}">
                                    {s name="AccountLinkPayment"}{/s}
                                </a>
                            </li>
                        {/block}

                        {* Link to the user orders *}
                        {block name="frontend_account_menu_link_orders"}
                            <li class="navigation--entry">
                                <a href="{url module='frontend' controller='account' action='orders'}" title="{s name="AccountLinkPreviousOrders"}{/s}" class="navigation--link{if $sAction == 'orders'} is--active{/if}">
                                    {s name="AccountLinkPreviousOrders"}{/s}
                                </a>
                            </li>
                        {/block}

                        {* Link to the user downloads *}
                        {block name="frontend_account_menu_link_downloads"}
                            {if {config name=showEsd}}
                                <li class="navigation--entry">
                                    <a href="{url module='frontend' controller='account' action='downloads'}" title="{s name="AccountLinkDownloads"}{/s}" class="navigation--link{if $sAction == 'downloads'} is--active{/if}">
                                        {s name="AccountLinkDownloads"}{/s}
                                    </a>
                                </li>
                            {/if}
                        {/block}

                        {* Link to the user product notes *}
                        {block name="frontend_account_menu_link_notes"}
                            <li class="navigation--entry">
                                <a href="{url module='frontend' controller='note'}" title="{s name="AccountLinkNotepad"}{/s}" class="navigation--link{if {controllerName} == 'note'} is--active{/if}">
                                    {s name="AccountLinkNotepad"}{/s}
                                </a>
                            </li>
                        {/block}

                        {* Link to the partner statistics *}
                        {block name="frontend_account_menu_link_partner_statistics"}
                            {if $sUserLoggedIn && !$sOneTimeAccount}
                                {action module='frontend' controller="account" action="partnerStatisticMenuItem"}
                            {/if}
                        {/block}

                        {* Logout action *}
                        {block name="frontend_account_menu_link_logout"}
                            <li class="navigation--entry">
                                <a href="{url module='frontend' controller='account' action='logout'}" title="{s name="AccountLinkLogout2"}{/s}" class="navigation--link link--logout">
                                    <i class="icon--logout"></i> {s name="AccountLinkLogout2"}{/s}
                                </a>
                            </li>
                        {/block}
                    {/block}
                </ul>
            {/block}
        </div>
    {/block}
</div>
{/if}
