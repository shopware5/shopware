{if $sComparisons}
    {block name='frontend_top_navigation_menu_entry'}
        <i class="icon--compare"></i> {s name="CompareInfoCount"}{/s}<span class="badge">{$sComparisons|@count}</span>
    {/block}
    {block name='frontend_compare_product_list_dropdown'}
        <ul class="compare--list is--rounded" data-product-compare-menu="true" role="menu">
            {foreach $sComparisons as $compare}
                {block name='frontend_compare_product_dropdown_entry'}
                <li class="service--entry" role="menuitem">
                    {block name='frontend_compare_product_dropdown_article_name'}
                        <a class="compare--link">{$compare.articlename|truncate:28}</a>
                    {/block}

                    {block name='frontend_compare_product_dropdown_article_link'}
                        <a class="btn btn--item-delete" href="{url controller='compare' action='delete_article' articleID=$compare.articleID}" rel="nofollow">
                            <i class="icon--cross"></i>
                        </a>
                    {/block}
                </li>
                {/block}
            {/foreach}
            {block name='frontend_compare_product_dropdown_action_start'}
                <li>
                    <a href="{url controller='compare' action='overlay' forceSecure}" data-modal-title="{s name="CompareInfoCount"}Produktvergleich{/s}" rel="nofollow" class="btn is--primary is--full is--icon-right btn--compare btn--compare-start">
                        {s name="CompareActionStart"}{/s}
                        <i class="icon--arrow-right"></i>
                    </a>
                </li>
            {/block}
            {block name='frontend_compare_product_dropdown_action_delete'}
                <li>
                    <a href="{url controller='compare' action='delete_all' forceSecure}" rel="nofollow" class="btn is--secondary is--full btn--compare btn--compare-delete">
                        {s name="CompareActionDelete"}{/s}
                    </a>
                </li>
            {/block}
        </ul>
    {/block}
{/if}