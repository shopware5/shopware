
<div {if $priceFacet.active}class="active"{/if} >
    Prices
    <span class="expandcollapse">+</span>
</div>


<div class="slideContainer">
    <ul>
        {if $priceFacet.active}
            <li class="active">
                {$priceFacet.range.min|currency} - {$priceFacet.range.max|currency}
            </li>
        {else}
            <li>
                <a href="{$range.link}" title="{$sCategoryInfo.name}">
                    {$priceFacet.range.min|currency} - {$priceFacet.range.max|currency}
                </a>
            </li>
        {/if}
        {if $priceFacet.active}
            <li class="close">
                <a href="{$priceFacet.removeLink}" title="{$sCategoryInfo.name}">
                    Show all prices
                </a>
            </li>
        {/if}
    </ul>
</div>