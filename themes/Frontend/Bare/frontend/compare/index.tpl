{if $sComparisons}
    {block name='frontend_top_navigation_menu_entry'}
        <i class="icon--compare"></i> {s name="CompareInfoCount"}{/s}<span class="compare--quantity">({$sComparisons|@count})</span>
    {/block}
    {block name='frontend_compare_product_list_dropdown'}
        <ul class="compare--list is--rounded" data-product-compare-menu="true" role="menu">
            {foreach $sComparisons as $compare}
                {block name='frontend_compare_product_dropdown_entry'}
                <li class="compare--entry" role="menuitem">
                    {block name='frontend_compare_product_dropdown_article_name'}
                        <a href="{url controller=detail sArticle=$compare.articleId|rewrite:$compare.articlename}" title="{$compare.articlename|escape}" class="compare--link">{$compare.articlename}</a>
                    {/block}

                    {block name='frontend_compare_product_dropdown_article_link'}
                        <a class="btn btn--item-delete" href="{url controller='compare' action='delete_article' articleID=$compare.articleID}" rel="nofollow">
                            <i class="icon--cross compare--icon-remove"></i>
                        </a>
                    {/block}
                </li>
                {/block}
            {/foreach}
            {block name='frontend_compare_product_dropdown_action_start'}
                <li>
                    <a href="{url controller='compare' action='overlay' forceSecure}" data-modal-title="{s name="CompareInfoCount"}{/s}" rel="nofollow" class="btn--compare btn--compare-start btn is--primary is--full is--small is--icon-right">
                        {s name="CompareActionStart"}{/s}
                        <i class="icon--arrow-right"></i>
                    </a>
                </li>
            {/block}
            {block name='frontend_compare_product_dropdown_action_delete'}
                <li>
                    <a href="{url controller='compare' action='delete_all' forceSecure}" rel="nofollow" class="btn--compare-delete btn--compare btn is--secondary is--small is--full">
                        {s name="CompareActionDelete"}{/s}
                    </a>
                </li>
            {/block}
        </ul>
    {/block}
{/if}