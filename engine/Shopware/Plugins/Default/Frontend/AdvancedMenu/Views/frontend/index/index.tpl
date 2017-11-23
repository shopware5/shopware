{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_navigation_categories_top_include"}
    {$smarty.block.parent}
    {block name="frontend_plugins_advanced_menu_outer"}
        {include file="frontend/plugins/advanced_menu/index.tpl"}
    {/block}
{/block}
