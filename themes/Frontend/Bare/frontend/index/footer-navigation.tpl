{namespace name="frontend/index/menu_footer"}

{* Service hotline *}
{block name="frontend_index_footer_column_service_hotline"}
    <div class="footer--column column--hotline is--first block">
        {block name="frontend_index_footer_column_service_hotline_headline"}
            <div class="column--headline">{s name="sFooterServiceHotlineHead"}{/s}</div>
        {/block}

        {block name="frontend_index_footer_column_service_hotline_content"}
            <div class="column--content">
                <p class="column--desc">{s name="sFooterServiceHotline"}{/s}</p>
            </div>
        {/block}
    </div>
{/block}

{block name="frontend_index_footer_column_service_menu"}
    <div class="footer--column column--menu block">
        {block name="frontend_index_footer_column_service_menu_headline"}
            <div class="column--headline">{s name="sFooterShopNavi1"}{/s}</div>
        {/block}

        {block name="frontend_index_footer_column_service_menu_content"}
            <nav class="column--navigation column--content">
                <ul class="navigation--list" role="menu">
                    {block name="frontend_index_footer_column_service_menu_before"}{/block}
                    {foreach $sMenu.bottom as $item}

                        {block name="frontend_index_footer_column_service_menu_entry"}
                            <li class="navigation--entry" role="menuitem">
                                <a class="navigation--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description|escape}"{if $item.target} target="{$item.target}"{/if}>
                                    {$item.description}
                                </a>

                                {* Sub categories *}
                                {if $item.childrenCount > 0}
                                    <ul class="navigation--list is--level1" role="menu">
                                        {foreach $item.subPages as $subItem}
                                            <li class="navigation--entry" role="menuitem">
                                                <a class="navigation--link" href="{if $subItem.link}{$subItem.link}{else}{url controller='custom' sCustom=$subItem.id title=$subItem.description}{/if}" title="{$subItem.description|escape}"{if $subItem.target} target="{$subItem.target}"{/if}>
                                                    {$subItem.description}
                                                </a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            </li>
                        {/block}
                    {/foreach}

                    {block name="frontend_index_footer_column_service_menu_after"}{/block}
                </ul>
            </nav>
        {/block}
    </div>
{/block}

{block name="frontend_index_footer_column_information_menu"}
    <div class="footer--column column--menu block">
        {block name="frontend_index_footer_column_information_menu_headline"}
            <div class="column--headline">{s name="sFooterShopNavi2"}{/s}</div>
        {/block}

        {block name="frontend_index_footer_column_information_menu_content"}
            <nav class="column--navigation column--content">
                <ul class="navigation--list" role="menu">
                    {block name="frontend_index_footer_column_information_menu_before"}{/block}
                        {foreach $sMenu.bottom2 as $item}

                            {block name="frontend_index_footer_column_information_menu_entry"}
                                <li class="navigation--entry" role="menuitem">
                                    <a class="navigation--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description|escape}"{if $item.target} target="{$item.target}"{/if}>
                                        {$item.description}
                                    </a>

                                    {* Sub categories *}
                                    {if $item.childrenCount > 0}
                                        <ul class="navigation--list is--level1" role="menu">
                                            {foreach $item.subPages as $subItem}
                                                <li class="navigation--entry" role="menuitem">
                                                    <a class="navigation--link" href="{if $subItem.link}{$subItem.link}{else}{url controller='custom' sCustom=$subItem.id title=$subItem.description}{/if}" title="{$subItem.description|escape}"{if $subItem.target} target="{$subItem.target}"{/if}>
                                                        {$subItem.description}
                                                    </a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    {/if}
                                </li>
                            {/block}
                        {/foreach}
                    {block name="frontend_index_footer_column_information_menu_after"}{/block}
                </ul>
            </nav>
        {/block}
    </div>
{/block}

{block name="frontend_index_footer_column_newsletter"}
    <div class="footer--column column--newsletter is--last block">
        {block name="frontend_index_footer_column_newsletter_headline"}
            <div class="column--headline">{s name="sFooterNewsletterHead"}{/s}</div>
        {/block}

        {block name="frontend_index_footer_column_newsletter_content"}
            <div class="column--content" data-newsletter="true">
                <p class="column--desc">
                    {s name="sFooterNewsletter"}{/s}
                </p>

                {block name="frontend_index_footer_column_newsletter_form"}
                    <form class="newsletter--form" action="{url controller='newsletter'}" method="post">
                        <input type="hidden" value="1" name="subscribeToNewsletter" />

                        {block name="frontend_index_footer_column_newsletter_form_field_wrapper"}
                            <div class="content">
                                {block name="frontend_index_footer_column_newsletter_form_field"}
                                    <input type="email" aria-label="{s name="IndexFooterNewsletterValue"}{/s}" name="newsletter" class="newsletter--field" placeholder="{s name="IndexFooterNewsletterValue"}{/s}" />
                                    {if {config name="newsletterCaptcha"} !== "nocaptcha"}
                                        <input type="hidden" name="redirect">
                                    {/if}
                                {/block}

                                {block name="frontend_index_footer_column_newsletter_form_submit"}
                                    <button type="submit" aria-label="{s name='IndexFooterNewsletterSubmit'}{/s}" class="newsletter--button btn">
                                        <i class="icon--mail"></i> <span class="button--text">{s name='IndexFooterNewsletterSubmit'}{/s}</span>
                                    </button>
                                {/block}
                            </div>
                        {/block}

                        {* Data protection information *}
                        {block name="frontend_index_footer_column_newsletter_privacy"}
                            {if {config name=ACTDPRTEXT} || {config name=ACTDPRCHECK}}
                                {$hideCheckbox=false}

                                {* If a captcha is active, the user has to accept the privacy statement on the newsletter page *}
                                {if {config name=newsletterCaptcha} !== "nocaptcha"}
                                    {$hideCheckbox=true}
                                {/if}

                                {include file="frontend/_includes/privacy.tpl" hideCheckbox=$hideCheckbox}
                            {/if}
                        {/block}
                    </form>
                {/block}
            </div>
        {/block}
    </div>
{/block}
