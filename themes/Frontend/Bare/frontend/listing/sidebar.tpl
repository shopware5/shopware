{extends file="frontend/index/sidebar.tpl"}

{block name="frontend_index_sidebar"}
    {if $theme.sidebarFilter}
        {block name='frontend_listing_sidebar'}
            <div class="listing--sidebar">
                {$smarty.block.parent}

                <div class="sidebar-filter">
                    <div class="sidebar-filter--content">
                        {if $criteria && $facets}
                            {include file="frontend/listing/actions/action-filter-panel.tpl"}
                        {/if}
                    </div>
                </div>
            </div>
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}