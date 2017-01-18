{extends file="frontend/index/sidebar.tpl"}

{block name="frontend_index_left_inner_container"}
    {if $theme.sidebarFilter && $criteria}
        {block name='frontend_index_listing_sidebar'}
            <div class="listing--sidebar">
                {$smarty.block.parent}

                <div class="sidebar-filter">
                    <div class="sidebar-filter--content">
                        {include file="frontend/listing/actions/action-filter-panel.tpl"}
                    </div>
                </div>
            </div>
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}