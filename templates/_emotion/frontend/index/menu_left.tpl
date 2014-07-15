{* Static sites *}
{if $sMenu.gLeft}
    <ul id="servicenav">
        <li class="heading">{se name="MenuLeftHeading"}Informationen{/se}</li>
        {foreach from=$sMenu.gLeft item=item}
            <li{if $item.active} class="active"{/if}>
                <a{if $item.active} class="active"{/if} href="{if $item.link}{$item.link}{else}{url controller=custom sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
                    {$item.description}
                </a>
                {if $item.active && $item.subPages}
                    <ul class="sub-pages">
                        {foreach $item.subPages as $subPage}
                            <li><a href="{url controller=custom sCustom=$subPage.id}" title="{$subPage.description}"{if $subPage.active} class="active"{/if}>{$subPage.description}</a></li>
                        {/foreach}
                    </ul>
                {/if}
            </li>
        {/foreach}
    </ul>
    <div class="left_categories_shadow"></div>
{/if}