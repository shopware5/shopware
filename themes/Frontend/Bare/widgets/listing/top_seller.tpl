{block name="widgets_listing_top_seller"}
    {if $sCharts|@count}
        {block name="widgets_listing_top_seller_panel"}
            <div class="topseller panel has--border is--rounded">
                {block name="widgets_listing_top_seller_panel_inner"}

                    {block name="widgets_listing_top_seller_title"}
                        <div class="topseller--title panel--title is--underline">
                            {s name="TopsellerHeading" namespace=frontend/plugins/index/topseller}{/s}
                        </div>
                    {/block}

                    {block name="widgets_listing_top_seller_slider"}
                        <div class="topseller--content panel--body product-slider" data-topseller-slider="true">
                            {block name="widgets_listing_top_seller_slider_container"}
                                <div class="product-slider--container">
                                    {block name="widgets_listing_top_seller_slider_container_inner"}
                                        {foreach $sCharts as $article}
                                            {block name="widgets_listing_top_seller_slider_container_include"}
                                                <div class="product-slider--item">
                                                    {include file="frontend/listing/box_article.tpl" sArticle=$article productBoxLayout="slider"}
                                                </div>
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