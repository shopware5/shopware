{namespace name="frontend/search/filter_category"}

<div class="results--filter panel has--border">
    {* Search headline *}
    {block name="frontend_search_fuzzy_headline"}
    <div class="filter--title panel--title is--underline">
        {s name="SearchFilterCategoryHeading"}Suchergebnisse filtern{/s}
    </div>
    {/block}

    {block name="frontend_search_fuzzy_filter"}
    <div class="filter--types panel--body block-group">

        {* Filter by categories *}
        {block name="frontend_search_fuzzy_category"}
            <div class="types--list category--list block">

            {* Filter by categories title *}
            {block name="frontend_search_fuzzy_category_title"}
                <h3 class="list--headline category-headline">{s name="SearchFilterByCategory"}Filtern nach Kategorien{/s}</h3>
            {/block}

            {* Category items *}
            {block name="frontend_search_fuzzy_category_items"}
                {foreach $sCategoriesTree as $sKey => $sCategorie}
                    {if $sKey != $sSearchResults.sLastCategory}
                        <a href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}" class="is--active" title="{$sCategorie.description}">
                            {$sCategorie.description} &raquo;
                        </a>
                    {else}
                        <span class="category-description">{$sCategorie.description}</span>
                    {/if}
                {/foreach}

                {if $sSearchResults.sCategories.0}
                    {partition assign=sCategoriesParts array=$sSearchResults.sCategories parts=2}

                    {foreach $sCategoriesParts as $sCategories}
                        <ul class="categories--list">
                            {foreach $sCategories as $sCategorie}
                                {if $sCategorie.count!=""}
                                    <li class="list--entry">
                                        <a class="list--entry-category-link" href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}" title="{$sCategorie.description} ({$sCategorie.count})">
                                            &raquo; {$sCategorie.description} <span class="category-count">({$sCategorie.count})</span>
                                        </a>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    {/foreach}
                {/if}
            {/block}

            {* Reset category filter *}
            {block name="frontend_search_fuzzy_category_reset"}
                {if $sRequests.sFilter.category}
                    <a href="{$sLinks.sFilter.category}" class="link--reset" title="{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}">
                        {s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}
                    </a>
                {/if}
            {/block}
        </div>
        {/block}

        {* Filter by suppliers *}
        {block name="frontend_search_fuzzy_supplier"}
            <div class="types--list supplier--list block">

                {* Filter by suppliers title *}
                {block name="frontend_search_fuzzy_suppliers_title"}
                    <h3 class="list--headline supplier-headline">{s name="SearchFilterBySupplier"}Filtern nach Herstellern{/s}</h3>
                {/block}

                {if $sSearchResults.sSuppliers}
                    {* Filter by supplier *}
                    {block name='frontend_search_filter_supplier'}
                        {$sSuppliersFirst = $sSearchResults.sSuppliers|@array_slice:0:10}
                        {$sSuppliersRest = $sSearchResults.sSuppliers|@array_slice:10}
                        <ul class="suppliers--list">
                            {if !$sRequests.sFilter.supplier}
                                {foreach $sSuppliersFirst as $supplier}
                                    <li class="list--entry">
                                        <a href="{$sLinks.sFilter.supplier}&sFilter_supplier={$supplier.id}" title="{$supplier.name} ({$supplier.count})">
                                            &raquo; {$supplier.name} <span class="supplier-count">({$supplier.count})</span>
                                        </a>
                                    </li>
                                {/foreach}

                                {if $sSuppliersRest}
                                    <form name="frmsup" method="POST" action="{$sLinks.sFilter.supplier}" class="form--suppliers-rest">
                                        <select name="sFilter_supplier" data-auto-submit="true" class="filter-supplier-select">
                                            <option value="">{s name='SearchLeftInfoSuppliers'}{/s}</option>
                                            {foreach $sSuppliersRest as $supplier}
                                                <option value="{$supplier.id}">{$supplier.name} ({$supplier.count})</option>
                                            {/foreach}
                                        </select>
                                    </form>
                                {/if}
                            {else}
                                <li class="is--active">{$sSearchResults.sSuppliers[$sRequests.sFilter.supplier].name}</li>
                                <li>
                                    <a class="link--reset" href="{$sLinks.sFilter.supplier}" title="{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}">
                                        {s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}
                                    </a>
                                </li>
                            {/if}
                        </ul>
                    {/block}
                {/if}
            </div>
        {/block}

        {* Filter by prices *}
        {block name="frontend_search_fuzzy_price"}
            <div class="types--list price--list block">
            {block name="frontend_search_fuzzy_prices_title"}
                <h3 class="list--headline price-headline">{s name="SearchFilterByPrice"}Filtern nach Preisen{/s}</h3>
            {/block}

            {if $sSearchResults.sPrices||$sRequests.sFilter.price}
                {* Filter by price *}
                {block name='frontend_search_fuzzy_filter_price'}
                    <ul class="prices--list">
                        {if !$sRequests.sFilter.price}
                            {foreach $sPriceFilter as $sKey => $sFilterPrice}
                                {if $sSearchResults.sPrices.$sKey}
                                    <li class="list--entry">
                                        <a href="{$sLinks.sFilter.price}&sFilter_price={$sKey}" title="{$sFilterPrice.start|currency} - {$sFilterPrice.end|currency} ({$sSearchResults.sPrices.$sKey})">
                                            &raquo; {$sFilterPrice.start|currency} - {$sFilterPrice.end|currency} <span class="prices-count">({$sSearchResults.sPrices.$sKey})</span>
                                        </a>
                                    </li>
                                {/if}
                            {/foreach}
                        {else}
                            <li class="is--active">{$sPriceFilter[$sRequests.sFilter.price].start|currency} - {$sPriceFilter[$sRequests.sFilter.price].end|currency}</li>
                            <li>
                                <a class="link--reset" href="{$sLinks.sFilter.price}" title="{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}">
                                    {s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}
                                </a>
                            </li>
                        {/if}
                    </ul>
                {/block}
            {/if}
        </div>
        {/block}
    </div>
    {/block}
</div>