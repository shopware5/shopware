{* Static sites *}
{function name=customPages level=0}
    <ul class="shop-sites--navigation sidebar--navigation navigation--list{if !$level} is--drop-down{/if} is--level{$level}" role="menu">
        {block name='frontend_index_left_menu_before'}{/block}

        {foreach $customPages as $item}
            {block name='frontend_index_left_menu_entry'}
                <li class="navigation--entry{if $item.active} is--active{/if}" role="menuitem">
                    <a class="navigation--link{if $item.active} is--active{/if}" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description|escape}" {if $item.target}target="{$item.target}"{/if}>
                        {$item.description}
                    </a>
                    {block name="frontend_index_categories_left_entry_subcategories"}

                        {if $item.active && $item.subPages}
                            {call name=customPages customPages=$item.subPages level=$level+1}
                        {/if}
                    {/block}
                </li>
            {/block}
        {/foreach}

        {block name='frontend_index_left_menu_after'}{/block}
    </ul>
{/function}

{if $sMenu.gLeft}
    <div class="shop-sites--container is--rounded">
        {block name='frontend_index_left_menu_headline'}
            <h2 class="shop-sites--headline navigation--headline">{s namespace='frontend/index/menu_left' name="MenuLeftHeadingInformation"}Informationen{/s}</h2>
        {/block}

        {call name=customPages customPages=$sMenu.gLeft}
    </div>
{/if}