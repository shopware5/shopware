{if $sMenu[$sGroup]}
    <ul class="service--list" role="menu">
        {foreach $sMenu[$sGroup] as $item}
            <li class="service--entry" role="menuitem">
                <a class="service--link" href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
                    {$item.description}
                </a>
            </li>
        {/foreach}
    </ul>
{/if}