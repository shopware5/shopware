{extends file='parent:frontend/listing/filter_properties.tpl'}

{* Filter properites *}
{block name="frontend_listing_filter_properties"}

{if $sProperties}
    {foreach $sProperties as $set}
        {foreach $set.groups as $group}

            <div {if $group.active}class="active"{/if} >
                {$group.name}
                <span class="expandcollapse">+</span>
            </div>

            <div class="slideContainer">
                <ul>
                    {foreach $group.options as $option}
                        {if $option.active}
                            <li class="active">{$option.name}</li>
                        {else}
                            <li>
                                <a href="{$option.link}" title="{$sCategoryInfo.name}">
                                    {$option.name} ({$option.total})
                                </a>
                            </li>
                        {/if}
                    {/foreach}
                    {if $group.active}
                        <li class="close">
                            <a href="{$group.removeLink}" title="{$sCategoryInfo.name}">
                                {se name='FilterLinkDefault'}{/se}
                            </a>
                        </li>
                    {/if}
                </ul>
            </div>
        {/foreach}
    {/foreach}
{/if}
{/block}

