{if $sMenu[$sGroup]}
<ul id="servicenavi">
<span class="arrow"></span>
    {foreach from=$sMenu[$sGroup] item=item}
        <li>
            <a href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
                {$item.description}
            </a>
        </li>
    {/foreach}
</ul>
{/if}