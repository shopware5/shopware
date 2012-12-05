{* Add the basic bundle view *}
{block name="frontend_detail_index_detail" prepend}
    {block name="frontend_detail_index_bundles"}
        {if $sBundles}
            {foreach $sBundles as $sBundle}
                <form method="POST" action="{url controller=bundle action=addBundleToBasket bundleId=$sBundle.id forceSecure}">
                    {include file="frontend/detail/bundle/container.tpl" sBundle=$sBundle}
                </form>
            {/foreach}
        {/if}
    {/block}
{/block}
