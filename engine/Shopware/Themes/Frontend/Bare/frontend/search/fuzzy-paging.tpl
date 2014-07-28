{extends file='frontend/listing/listing_actions.tpl'}

{namespace name="frontend/listing/listing_actions"}

{* Filter by Sort *}
{block name='frontend_listing_actions_sort'}
    <form class="action--sort action--content block" method="get" action="{$sLinks.sSort}">

        {* Necessary to reset the page to the first one *}
        <input type="hidden" name="sPage" value="1">

        {* Sorting label *}
        {block name='frontend_listing_actions_sort_label'}
            <label class="sort--label action--label">{s name='ListingLabelSort'}{/s}</label>
        {/block}

        {* Sorting field *}
        {block name='frontend_listing_actions_sort_field'}
            <select name="sSort" class="sort--field action--field" data-auto-submit="true" data-class="sort--select">
                <option value="1"{if $sRequests.sSort eq 1} selected="selected"{/if}>{s name='ListingSortRelease'}{/s}</option>
                <option value="2"{if $sRequests.sSort eq 2} selected="selected"{/if}>{s name='ListingSortRating'}{/s}</option>
                <option value="3"{if $sRequests.sSort eq 3} selected="selected"{/if}>{s name='ListingSortPriceLowest'}{/s}</option>
                <option value="4"{if $sRequests.sSort eq 4} selected="selected"{/if}>{s name='ListingSortPriceHighest'}{/s}</option>
                <option value="5"{if $sRequests.sSort eq 5} selected="selected"{/if}>{s name='ListingSortName'}{/s}</option>
                {block name='frontend_listing_actions_sort_values'}{/block}
            </select>
        {/block}
    </form>
{/block}

{* View label *}
{block name="frontend_listing_actions_change_layout_label"}{/block}

{* Link - Table view *}
{block name="frontend_listing_actions_change_layout_link_table"}{/block}

{* Link - List view *}
{block name="frontend_listing_actions_change_layout_link_list"}{/block}

{* Articles per page *}
{block name='frontend_listing_actions_items_per_page'}
    {if $sPerPage}
        <form class="action--per-page action--content block" method="get" action="{url action=index sSearch=$sRequests.sSearch sSort=$sRequests.sSort sPage=1 sTemplate=$sRequests.sTemplate}">
            {foreach $categoryParams as $value}
                {if $value@key == 'sPerPage'}
                    {continue}
                {/if}

                <input type="hidden" name="{$value@key}" value="{$value}">
            {/foreach}

            {* Necessary to reset the page to the first one *}
            <input type="hidden" name="sPage" value="1">

            {* Per page label *}
            {block name='frontend_listing_actions_items_per_page_label'}
                <label class="per-page--label action--label">{s name='ListingLabelItemsPerPage'}{/s}</label>
            {/block}

            {* Per page field *}
            {block name='frontend_listing_actions_items_per_page_field'}
                <select name="sPerPage" class="per-page--field action--field" data-auto-submit="true" data-class="per-page--select">
                    {foreach $sPerPage as $perPage}
                        <option value="{$perPage}" {if $sRequests.sPerPage == $perPage}selected="selected"{/if}>{$perPage}</option>
                    {/foreach}
                    {block name='frontend_listing_actions_per_page_values'}{/block}
                </select>
            {/block}
        </form>
    {/if}
{/block}

{* Paging for listing actions *}
{block name='frontend_listing_actions_paging'}

    {if $sPages.pages|@count != 0}
        <div class="listing--paging panel--paging">

            {* Pagination - Previous page *}
            {block name='frontend_listing_actions_paging_previous'}
                {if isset($sPages.before)}
                    <a href="{$sLinks.sPage}&sPage={$sPages.before}" title="{s name='ListingLinkPrevious'}{/s}" class="pagination--link paging--prev">{s name="ListingTextPrevious"}&lt;{/s}</a>
                {/if}
            {/block}

            {* Pagination numbers *}
            {block name='frontend_listing_actions_paging_numbers'}
                {foreach $sPages.pages as $page}
                    {if $page<$sRequests.currentPage+4 AND $page>$sRequests.currentPage-4}
                        {if $sRequests.currentPage==$page}
                            <a title="{$sCategoryInfo.name}" class="pagination--link is--active">{$page}</a>
                        {else}
                            <a href="{$sLinks.sPage}&sPage={$page}" class="pagination--link">{$page}</a>
                        {/if}
                    {elseif $page==$sRequests.currentPage+4 OR $page==$sRequests.currentPage-4}
                        <span class="pagination--link pagination--more">...</span>
                    {/if}
                {/foreach}
            {/block}

            {* Pagination - Next page *}
            {block name='frontend_listing_actions_paging_next'}
                {if $sPages.next}
                    <a href="{$sLinks.sPage}&sPage={$sPages.next}" title="{s name='ListingLinkNext'}{/s}" class="pagination--link paging--next">{s name="ListingTextNext"}&gt;{/s}</a>
                {/if}
            {/block}

            {* Page counter *}
            {block name='frontend_listing_actions_count'}
                <div class="pagination--display">
                    {s name="ListingTextSite"}Seite{/s} <strong>{if $sPage}{$sPage}{else}1{/if}</strong> {s name="ListingTextFrom"}von{/s} <strong>{$sPages|count-1}</strong>
                </div>
            {/block}
        </div>
    {/if}
{/block}