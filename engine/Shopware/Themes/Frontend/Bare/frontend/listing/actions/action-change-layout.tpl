{* Layout switcher which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

{if !$sCategoryContent.noViewSelect}
    <div class="action--change-layout action--content block">

        {* Layout switcher label *}
        {block name="frontend_listing_actions_change_layout_label"}
            <label class="change-layout--label action--label">{s name='ListingActionsSettingsTitle'}Darstellung:{/s}</label>
        {/block}

        {* Link - Table view *}
        {block name="frontend_listing_actions_change_layout_link_table"}
            <a href="#?p=1&l=table"
			   data-action-link="true"
			   class="btn action--link link--table-view{if $sBoxMode=='table'} is--active{/if}"
			   title="{"{s name='ListingActionsSettingsTable'}Tabellen-Ansicht{/s}"|escape}">
                <span class="action--link-text">{s name='ListingActionsSettingsTable'}Tabellen-Ansicht{/s}</span>
            </a>
        {/block}

        {* Link - List view *}
        {block name="frontend_listing_actions_change_layout_link_list"}
            <a href="#?p=1&l=list"
			   data-action-link="true"
			   class="btn action--link link--list-view{if $sBoxMode=='list'} is--active{/if}"
			   title="{"{s name='ListingActionsSettingsList'}Listen-Ansicht{/s}"|escape}">
                <span class="action--link-text">{s name='ListingActionsSettingsList'}Listen-Ansicht{/s}</span>
            </a>
        {/block}
    </div>
{/if}
