{* Layout switcher which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

{if !$sCategoryContent.noViewSelect}
    <div class="action--change-layout action--content block">

        {assign var="templateLinks" value=array()}
        {foreach $categoryParams as $key => $value}
            {if $key == 'sTemplate' || $key == $shortParameters.sTemplate}
                {continue}
            {/if}
            {$templateLinks[$key] = $value}
        {/foreach}

        {* Layout switcher label *}
        {block name="frontend_listing_actions_change_layout_label"}
            <label class="change-layout--label action--label">{s name='ListingActionsSettingsTitle'}Darstellung:{/s}</label>
        {/block}

        {* Link - Table view *}
        {block name="frontend_listing_actions_change_layout_link_table"}
            <a href="{url params=$templateLinks sViewport='cat' sCategory=$sCategoryContent.id sPage=1 sTemplate='table'}" class="btn action--link link--table-view{if $sBoxMode=='table'} is--active{/if}" title="{"{s name='ListingActionsSettingsTable'}Tabellen-Ansicht{/s}"|escape}">
                <span class="action--link-text">{s name='ListingActionsSettingsTable'}Tabellen-Ansicht{/s}</span>
            </a>
        {/block}

        {* Link - List view *}
        {block name="frontend_listing_actions_change_layout_link_list"}
            <a href="{url params=$templateLinks sViewport='cat' sCategory=$sCategoryContent.id sPage=1 sTemplate='list'}" class="btn action--link link--list-view{if $sBoxMode=='list'} is--active{/if}" title="{"{s name='ListingActionsSettingsList'}Listen-Ansicht{/s}"|escape}">
                <span class="action--link-text">{s name='ListingActionsSettingsList'}Listen-Ansicht{/s}</span>
            </a>
        {/block}
    </div>
{/if}