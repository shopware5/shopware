{block name='widgets_index_menu'}
    {if $sMenu[$sGroup]}
        <ul class="service--list is--rounded" role="menu">
            {foreach $sMenu[$sGroup] as $item}
                <li class="service--entry" role="menuitem">
                    <a class="service--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description|escape}" {if $item.target}target="{$item.target}"{/if}>
                        {$item.description}
                    </a>
                </li>
            {/foreach}
        </ul>
    {/if}
{/block}
