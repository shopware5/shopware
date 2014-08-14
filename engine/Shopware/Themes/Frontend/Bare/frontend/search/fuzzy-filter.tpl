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
                        {block name='frontend_search_filter_category_active_item'}
                            {if $sKey != $sSearchResults.sLastCategory}
                                <a href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}" class="link--category is--active" title="{$sCategorie.description|escape}">
                                    {$sCategorie.description|escape} &raquo;
                                </a>
                            {else}
                                <span class="category-description">{$sCategorie.description|escape}</span>
                            {/if}
                        {/block}
                    {/foreach}

                    {if $sSearchResults.sCategories.0}
                        {partition assign=sCategoriesParts array=$sSearchResults.sCategories parts=2}

                        {foreach $sCategoriesParts as $sCategories}
                            <ul class="categories--list list--unstyled">
                                {foreach $sCategories as $sCategorie}
                                    {if $sCategorie.count!=""}
                                        {block name='frontend_search_filter_category_item'}
                                            <li class="list--entry">
                                                <a class="entry--category-link" href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}" title="{$sCategorie.description|escape} ({$sCategorie.count})">
                                                    &raquo; {$sCategorie.description|escape} <span class="category-count">({$sCategorie.count})</span>
                                                </a>
                                            </li>
                                        {/block}
                                    {/if}
                                {/foreach}
                            </ul>
                        {/foreach}
                    {/if}
                {/block}

                {* Reset category filter *}
                {block name="frontend_search_fuzzy_category_reset"}
                    {if $sRequests.sFilter.category}
                        <a href="{$sLinks.sFilter.category}" class="link--reset" title="{"{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}"|escape}">
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
                            <ul class="suppliers--list list--unstyled">
                                {if !$sRequests.sFilter.supplier}
                                    {foreach $sSuppliersFirst as $supplier}
                                        {block name='frontend_search_filter_supplier_item'}
                                            <li class="list--entry">
                                                <a class="entry--supplier-link" href="{$sLinks.sFilter.supplier}&sFilter_supplier={$supplier.id}" class="link--supplier" title="{$supplier.name|escape} ({$supplier.count})">
                                                    &raquo; {$supplier.name} <span class="supplier-count">({$supplier.count})</span>
                                                </a>
                                            </li>
                                        {/block}
                                    {/foreach}

                                    {if $sSuppliersRest}
                                        {block name='frontend_search_filter_supplier_dropdown'}
                                            <form name="frmsup" method="POST" action="{$sLinks.sFilter.supplier}" class="form--suppliers-rest">
                                                <select name="sFilter_supplier" data-auto-submit="true" class="filter-supplier-select">
                                                    <option value="">{s name='SearchLeftInfoSuppliers'}{/s}</option>
                                                    {foreach $sSuppliersRest as $supplier}
                                                        <option value="{$supplier.id}">{$supplier.name} ({$supplier.count})</option>
                                                    {/foreach}
                                                </select>
                                            </form>
                                        {/block}
                                    {/if}
                                {else}
                                    {block name='frontend_search_filter_supplier_reset'}
                                        <li class="is--active">{$sSearchResults.sSuppliers[$sRequests.sFilter.supplier].name}</li>
                                        <li class="list--entry">
                                            <a class="link--reset" href="{$sLinks.sFilter.supplier}" title="{"{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}"|escape}">
                                                {s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}
                                            </a>
                                        </li>
                                    {/block}
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
                            <ul class="prices--list list--unstyled">
                                {if !$sRequests.sFilter.price}
                                    {foreach $sPriceFilter as $sKey => $sFilterPrice}
                                        {if $sSearchResults.sPrices.$sKey}
                                            {block name='frontend_search_filter_price_item'}
                                                <li class="list--entry">
                                                    <a class="entry--price-link" href="{$sLinks.sFilter.price}&sFilter_price={$sKey}" title="{$sFilterPrice.start|currency} - {$sFilterPrice.end|currency} ({$sSearchResults.sPrices.$sKey})">
                                                        &raquo; {$sFilterPrice.start|currency} - {$sFilterPrice.end|currency} <span class="prices-count">({$sSearchResults.sPrices.$sKey})</span>
                                                    </a>
                                                </li>
                                            {/block}
                                        {/if}
                                    {/foreach}
                                {else}
                                    {block name='frontend_search_filter_price_reset'}
                                        <li class="is--active">{$sPriceFilter[$sRequests.sFilter.price].start|currency} - {$sPriceFilter[$sRequests.sFilter.price].end|currency}</li>
                                        <li class="list--entry">
                                            <a class="link--reset" href="{$sLinks.sFilter.price}" title="{"{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}"|escape}">
                                                {s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}
                                            </a>
                                        </li>
                                    {/block}
                                {/if}
                            </ul>
                        {/block}
                    {/if}
                </div>
            {/block}
        </div>
    {/block}
</div>