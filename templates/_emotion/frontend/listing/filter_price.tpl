
<div {if $priceFacet.active}class="active"{/if} >
    Prices
    <span class="expandcollapse">+</span>
</div>


<div class="slideContainer">
    <ul>
        {foreach $priceFacet.prices as $range}

            {if $range.active}
                <li class="active">
                    {$range.priceMin|currency} - {$range.priceMax|currency} ({$range.total})
                </li>
            {else}
                <li>
                    <a href="{$range.link}" title="{$sCategoryInfo.name}">
                        {$range.priceMin|currency} - {$range.priceMax|currency} ({$range.total})
                    </a>
                </li>
            {/if}
        {/foreach}
        {if $priceFacet.active}
            <li class="close">
                <a href="{$priceFacet.removeLink}" title="{$sCategoryInfo.name}">
                    Show all prices
                </a>
            </li>
        {/if}
    </ul>
</div>