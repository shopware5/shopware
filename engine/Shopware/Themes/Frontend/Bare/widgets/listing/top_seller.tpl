{block name="widgets_listing_top_seller"}
    {if $sCharts|@count}
        {block name="widgets_listing_top_seller_panel"}
            <div class="topseller panel has--border">
                {block name="widgets_listing_top_seller_panel_inner"}

                    {block name="widgets_listing_top_seller_title"}
                        <div class="topseller--title panel--title is--underline">
                            {s name="TopsellerHeading" namespace=frontend/plugins/index/topseller}{/s}
                        </div>
                    {/block}

                    {block name="widgets_listing_top_seller_slider"}
                        <div class="topseller--content panel--body product-slider" data-product-slider="true">
                            {block name="widgets_listing_top_seller_slider_container"}
                                <div class="product-slider--container">
                                    {block name="widgets_listing_top_seller_slider_container_inner"}
                                        {foreach $sCharts as $article}
                                            {block name="widgets_listing_top_seller_slider_container_include"}
                                                {include file="frontend/listing/product-box/box-product-slider.tpl"}
                                            {/block}
                                        {/foreach}
                                    {/block}
                                </div>
                            {/block}
                        </div>
                    {/block}
                {/block}
            </div>
        {/block}
    {/if}
{/block}