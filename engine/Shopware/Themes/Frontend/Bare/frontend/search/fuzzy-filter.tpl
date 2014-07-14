{namespace name="frontend/search/filter_category"}

<div class="results--filter panel has--border">
    {block name="frontend_search_fuzzy_headline"}
    <div class="filter--title panel--title is--underline">
        {s name="SearchFilterCategoryHeading"}Suchergebnisse filtern{/s}
    </div>
    {/block}

    {block name="frontend_search_fuzzy_filter"}
    <div class="filter--category panel--body block-group">
        {* Filter by categories *}
        {block name="frontend_search_fuzzy_category"}
        <div class="category--list block">
            {block name="frontend_search_fuzzy_category_title"}
                <h3>{s name="SearchFilterByCategory"}Filtern nach Kategorien{/s}</h3>
            {/block}

            {block name="frontend_search_fuzzy_category_items"}
                {foreach $sCategoriesTree as $sKey => $sCategorie}
                    {if $sKey != $sSearchResults.sLastCategory}
                        <a href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}" class="active">
                            {$sCategorie.description} &raquo;
                        </a>
                    {else}
                        <span>{$sCategorie.description}</span>
                    {/if}
                {/foreach}

                {if $sSearchResults.sCategories.0}
                    {partition assign=sCategoriesParts array=$sSearchResults.sCategories parts=2}

                    {foreach $sCategoriesParts as $sCategories}
                        <ul class="categories--list">
                            {foreach $sCategories as $sCategorie}
                                {if $sCategorie.count!=""}
                                    <li class="list--entry">
                                        <a class="list--entry-category-link" href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}">
                                            &raquo; {$sCategorie.description} ({$sCategorie.count})
                                        </a>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    {/foreach}
                {/if}
            {/block}

            {block name="frontend_search_fuzzy_category_reset"}
                {if $sRequests.sFilter.category}
                    <a href="{$sLinks.sFilter.category}" class="link--reset">{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}</a>
                {/if}
            {/block}
        </div>
        {/block}

        {* Filter by suppliers *}
        {block name="frontend_search_fuzzy_supplier"}
        <div class="supplier--list block">
            {block name="frontend_search_fuzzy_suppliers_title"}
                <h3>{s name="SearchFilterBySupplier"}Filtern nach Herstellern{/s}</h3>
            {/block}

            {if $sSearchResults.sSuppliers}
                {* Filter by supplier *}
                {block name='frontend_search_filter_supplier'}
                    {assign var=sSuppliersFirst value=$sSearchResults.sSuppliers|@array_slice:0:10}
                    {assign var=sSuppliersRest value=$sSearchResults.sSuppliers|@array_slice:10}
                    <ul class="suppliers--list">
                        {if !$sRequests.sFilter.supplier}
                            {foreach $sSuppliersFirst as $supplier}
                                <li class="list--entry">
                                    <a href="{$sLinks.sFilter.supplier}&sFilter_supplier={$supplier.id}">&raquo; {$supplier.name} ({$supplier.count})</a>
                                </li>
                            {/foreach}

                            {if $sSuppliersRest}
                                <form name="frmsup" method="POST" action="{$sLinks.sFilter.supplier}">
                                    <select name="sFilter_supplier" data-auto-submit="true">
                                        <option value="">{s name='SearchLeftInfoSuppliers'}{/s}</option>
                                        {foreach $sSuppliersRest as $supplier}
                                            <option value="{$supplier.id}">{$supplier.name} ({$supplier.count})</option>
                                        {/foreach}
                                    </select>
                                </form>
                            {/if}
                        {else}
                            <li class="is--active">{$sSearchResults.sSuppliers[$sRequests.sFilter.supplier].name}</li>
                            <a class="link--reset" href="{$sLinks.sFilter.supplier}">{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}</a>
                        {/if}
                    </ul>
                {/block}
            {/if}
        </div>
        {/block}

        {* Filter by prices *}
        {block name="frontend_search_fuzzy_price"}
        <div class="price--list block">
            <h3>{s name="SearchFilterByPrice"}Filtern nach Preisen{/s}</h3>

            {* Filter by price *}
            {if $sSearchResults.sPrices||$sRequests.sFilter.price}
                {block name='frontend_search_fuzzy_filter_price'}
                    <ul>
                        {if !$sRequests.sFilter.price}
                            {foreach $sPriceFilter as $sKey => $sFilterPrice}
                                {if $sSearchResults.sPrices.$sKey}
                                <li>
                                    <a href="{$sLinks.sFilter.price}&sFilter_price={$sKey}">&raquo;
                                        {$sFilterPrice.start|currency} - {$sFilterPrice.end|currency} ({$sSearchResults.sPrices.$sKey})
                                        {if $sFilterActive.price}{/if}
                                    </a>
                                </li>
                                {/if}
                            {/foreach}
                        {else}
                            <li class="is--active">{$sPriceFilter[$sRequests.sFilter.price].start|currency} - {$sPriceFilter[$sRequests.sFilter.price].end|currency}</li>
                            <a class="link--reset" href="{$sLinks.sFilter.price}">{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}</a>
                        {/if}
                    </ul>
                {/block}
            {/if}
        </div>
        {/block}
    </div>
    {/block}
</div>