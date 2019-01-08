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
                        {s name="ListingLinkPrevious" assign="snippetListingLinkPrevious"}{/s}
                        <a href="{$sPages.previous}" title="{$snippetListingLinkPrevious|escape}" class="paging--link paging--prev">
                            <i class="icon--arrow-left"></i>
                        </a>
                    {/if}
                {/block}

                {* Pagination - current page *}
                {block name='frontend_listing_actions_paging_numbers'}
                    <a title="{$sCategoryContent.name|escape}" class="paging--link is--active">{$sPage}</a>
                {/block}

                {* Pagination - Next page *}
                {block name='frontend_listing_actions_paging_next'}
                    {if $sPage < $sNumberPages}
                        {s name="ListingLinkNext" assign="snippetListingLinkNext"}{/s}
                        <a href="{$sPages.next}" title="{$snippetListingLinkNext|escape}" class="paging--link paging--next">
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
