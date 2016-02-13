{namespace name="frontend/listing/listing_actions"}

{block name='frontend_listing_actions_top'}

    <div class="listing--actions block-group">

        {if $sNumberPages > 1}
            <div class="listing--paging panel--paging">

                {* Pagination label *}
                {block name='frontend_listing_actions_paging_label'}{/block}

                {* Pagination - Previous page *}
                {block name='frontend_listing_actions_paging_previous'}
                    {if $sPage > 1}
                        <a href="{$sPages.previous|rewrite:$sCategoryInfo.name}" title="{"{s name='ListingLinkPrevious'}{/s}"|escape}" class="paging--link paging--prev" rel="{s name="BlogListingPageSEORel"}noindex,follow{/s}">
                            <i class="icon--arrow-left"></i>
                        </a>
                    {/if}
                {/block}

                {* Pagination - current page *}
                {block name='frontend_listing_actions_paging_numbers'}
                    {foreach from=$sPages.numbers item=page}
                        {if $page.value<$sPage+4 AND $page.value>$sPage-4}
                            {if $page.markup AND (!$sOffers OR $sPage)}
                                <a title="{$sCategoryInfo.name|escape} {s name="BlogListingPageName"}Seite{/s} {$page.value} {s name="BlogListingPageNameVon"}von{/s} {$sNumberPages}" class="paging--link is--active" rel="{s name="BlogListingPageSEORel"}noindex,follow{/s}">{$page.value}</a>
                            {else}
                                <a title="{$sCategoryInfo.name|escape} {s name="BlogListingPageName"}Seite{/s} {$page.value} {s name="BlogListingPageNameVon"}von{/s} {$sNumberPages}" href="{$page.link|rewrite:$sCategoryInfo.name}" class="paging--link" rel="{s name="BlogListingPageSEORel"}noindex,follow{/s}">{$page.value}</a>
                            {/if}
                        {/if}
                    {/foreach}
                {/block}

                {* Pagination - Next page *}
                {block name='frontend_listing_actions_paging_next'}
                    {if $sPage < $sNumberPages}
                        <a href="{$sPages.next|rewrite:$sCategoryInfo.name}" title="{"{s name='ListingLinkNext'}{/s}"|escape}" class="paging--link paging--next" rel="{s name="BlogListingPageSEORel"}noindex,follow{/s}">
                            <i class="icon--arrow-right"></i>
                        </a>
                    {/if}
                {/block}

                {* Pagination - Number of pages *}
                {block name='frontend_listing_actions_count'}
                    <span class="paging--display">
                        {s name="ListingTextFrom"}{/s} <strong>{$sNumberPages}</strong>
                    </span>
                {/block}
            </div>
        {/if}

    </div>
{/block}