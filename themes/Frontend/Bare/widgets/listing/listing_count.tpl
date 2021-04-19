<div id="result">
    {if $listing}
        <div id="listing">
            {$listing}
        </div>
    {/if}

    {if $pagination}
        <div id="pagination">
            {$pagination}
        </div>
    {/if}

    {if $facets}
        <div id="facets">
            {$facets|@json_encode}
        </div>
    {/if}
</div>
