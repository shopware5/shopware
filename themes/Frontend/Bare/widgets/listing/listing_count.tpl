{block name="widgets_listing_listing_count"}
    <div id="result">
        {block name="widgets_listing_listing_count_result"}
            {block name="widgets_listing_listing_count_result_listing"}
                {if $listing}
                    <div id="listing">
                        {block name="widgets_listing_listing_count_include_listing"}
                            {include file="frontend/listing/listing_ajax.tpl"}
                        {/block}
                    </div>
                {/if}
            {/block}

            {block name="widgets_listing_listing_count_result_pagination"}
                {if $pagination}
                    <div id="pagination">
                        {block name="widgets_listing_listing_count_include_pagination"}
                            {include file="frontend/listing/actions/action-pagination.tpl"}
                        {/block}
                    </div>
                {/if}
            {/block}

            {block name="widgets_listing_listing_count_result_facets"}
                {if $facets}
                    <div id="facets">
                        {block name="widgets_listing_listing_count_include_facets"}
                            {$facets|@json_encode|escape}
                        {/block}
                    </div>
                {/if}
            {/block}
        {/block}
    </div>
{/block}
