{namespace name="frontend/search/filter_category"}

<div class="results--filter panel has--border" style="margin-top: 20px">
    <div class="filter--title panel--title is--underline">
        {s name="SearchFilterCategoryHeading"}Suchergebnisse filtern{/s}
    </div>

    <div class="filter--category panel--body block-group">
        {* Filter by categories *}
        <div class="category--list block">
            <h3>Filtern nach Kategorien</h3>

            {foreach from=$sCategoriesTree key=sKey item=sCategorie}
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

                {foreach from=$sCategoriesParts item=sCategories}
                    <ul>
                        {foreach from=$sCategories item=sCategorie}
                            {if $sCategorie.count!=""}
                                <li>
                                    <a href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}">
                                        &raquo; {$sCategorie.description} ({$sCategorie.count})
                                    </a>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                {/foreach}
            {/if}

            {if $sRequests.sFilter.category}
                <a href="{$sLinks.sFilter.category}" class="link--reset">{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}</a>
            {/if}
        </div>

        {* Filter by suppliers *}
        <div class="supplier--list block">
            <h3>Filtern nach Herstellern</h3>

            {if $sSearchResults.sSuppliers}

                {* Filter by supplier *}
                {block name='frontend_search_filter_supplier'}
                    <div class="searchbox">
                        {assign var=sSuppliersFirst value=$sSearchResults.sSuppliers|@array_slice:0:10}
                        {assign var=sSuppliersRest value=$sSearchResults.sSuppliers|@array_slice:10}

                        <ul>
                            {if !$sRequests.sFilter.supplier}
                                {foreach from=$sSuppliersFirst item=supplier}
                                    <li><a href="{$sLinks.sFilter.supplier}&sFilter_supplier={$supplier.id}">&raquo; {$supplier.name} ({$supplier.count})</a></li>
                                {/foreach}

                                {if $sSuppliersRest}
                                    <form name="frmsup" method="POST" action="{$sLinks.sFilter.supplier}" id="frmsup">
                                        <select name="sFilter_supplier" class="auto_submit">
                                            <option value="">{se name='SearchLeftInfoSuppliers'}{/se}</option>
                                            {foreach from=$sSuppliersRest item=supplier}
                                                <option value="{$supplier.id}">{$supplier.name} ({$supplier.count})</option>
                                            {/foreach}
                                        </select>
                                    </form>
                                {/if}
                            {else}
                                <li class="active">{$sSearchResults.sSuppliers[$sRequests.sFilter.supplier].name}</li>
                                <li class="showall"><a class="link--reset" href="{$sLinks.sFilter.supplier}">{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}</a></li>
                            {/if}
                        </ul>

                    </div>
                {/block}
            {/if}
        </div>

        {* Filter by prices *}
        <div class="price--list block">
            <h3>Filtern nach Preisen</h3>

            {* Filter by price *}
            {if $sSearchResults.sPrices||$sRequests.sFilter.price}
                {block name='frontend_search_filter_price'}
                    <div class="searchbox">
                        <ul>
                            {if !$sRequests.sFilter.price}
                                {foreach from=$sPriceFilter item=sFilterPrice key=sKey}
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
                                <li class="active">{$sPriceFilter[$sRequests.sFilter.price].start|currency} - {$sPriceFilter[$sRequests.sFilter.price].end|currency}</li>
                                <li class="showall"><a class="link--reset" href="{$sLinks.sFilter.price}">{s name='SearchFilterLinkDefault'}Alle Kategorien anzeigen{/s}</a></li>
                            {/if}
                        </ul>
                    </div>
                {/block}
            {/if}
        </div>
    </div>
</div>