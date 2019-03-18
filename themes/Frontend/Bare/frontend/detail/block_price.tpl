{block name='frontend_detail_data_block_prices_start'}
    <div class="block-prices--container{if $hidden && !$sArticle.selected} is--hidden{/if} block-price--{$sArticle.ordernumber}">

        {$hasReferencePrice = ($sArticle.referenceprice > 0 && $sArticle.referenceunit)}

        {block name="frontend_detail_data_block_prices_table"}
            <table class="block-prices--table">
                {block name="frontend_detail_data_block_prices_table_inner"}
                    {block name="frontend_detail_data_block_prices_table_head"}
                        <thead class="block-prices--head">
                            {block name="frontend_detail_data_block_prices_table_head_inner"}
                                <tr class="block-prices--row">
                                    {block name="frontend_detail_data_block_prices_table_head_row"}
                                        {block name="frontend_detail_data_block_prices_table_head_cell_quantity"}
                                            <th class="block-prices--cell">
                                                {s namespace="frontend/detail/data" name="DetailDataColumnQuantity"}{/s}
                                            </th>
                                        {/block}
                                        {block name="frontend_detail_data_block_prices_table_head_cell_price"}
                                            <th class="block-prices--cell">
                                                {s namespace="frontend/detail/data" name="DetailDataColumnPrice"}{/s}
                                            </th>
                                        {/block}
                                        {if $hasReferencePrice}
                                            {block name="frontend_detail_data_block_prices_table_head_cell_reference_price"}
                                                <th class="block-prices--cell">
                                                    {s namespace="frontend/detail/data" name="DetailDataColumnReferencePrice"}{/s}
                                                </th>
                                            {/block}
                                        {/if}
                                    {/block}
                                </tr>
                            {/block}
                        </thead>
                    {/block}

                    {block name="frontend_detail_data_block_prices_table_body"}
                        <tbody class="block-prices--body">
                            {block name="frontend_detail_data_block_prices_table_body_inner"}
                                {foreach $sArticle.sBlockPrices as $blockPrice}
                                    {block name='frontend_detail_data_block_prices'}
                                        <tr class="block-prices--row {cycle values="is--primary,is--secondary"}" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                            {block name="frontend_detail_data_block_prices_table_body_row"}
                                                {block name="frontend_detail_data_block_prices_table_body_cell_quantity"}
                                                    <td class="block-prices--cell">

                                                        <meta itemprop="priceCurrency" content="{$Shop->getCurrency()->getCurrency()}" />
                                                        <meta itemprop="price" content="{$blockPrice.price}" />
                                                        <link itemprop="availability" href="http://schema.org/InStock" />

                                                        {if $blockPrice.from == 1}
                                                            {s namespace="frontend/detail/data" name="DetailDataInfoUntil"}{/s}
                                                            <span class="block-prices--quantity">{$blockPrice.to}</span>
                                                        {else}
                                                            {s namespace="frontend/detail/data" name="DetailDataInfoFrom"}{/s}
                                                            <span class="block-prices--quantity">{$blockPrice.from}</span>
                                                        {/if}
                                                    </td>
                                                {/block}
                                                {block name="frontend_detail_data_block_prices_table_body_cell_price"}
                                                    <td class="block-prices--cell">
                                                        {$blockPrice.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                                                    </td>
                                                {/block}
                                                {if $hasReferencePrice}
                                                    {block name="frontend_detail_data_block_prices_table_body_cell_reference_price"}
                                                        <td class="block-prices--cell">
                                                            {$blockPrice.referenceprice|currency}
                                                            {s name="Star" namespace="frontend/listing/box_article"}{/s} /
                                                            {$sArticle.referenceunit} {$sArticle.sUnit.description}
                                                        </td>
                                                    {/block}
                                                {/if}
                                            {/block}
                                        </tr>
                                    {/block}
                                {/foreach}
                            {/block}
                        </tbody>
                    {/block}
                {/block}
            </table>
        {/block}

        {if $sArticle.pricegroupCrossProduct}
            {block name="frontend_detail_data_block_prices_pricegroup_cross_product"}
                {assign
                    var='priceGroupQuantityParagraph'
                    value="{s name="priceGroupCartItemsQuantity" namespace="frontend/detail/data"}You already have %s items of the price scale %s in your basket.{/s}"
                }
                {assign
                    var='priceGroupQuantity'
                    value="<strong id='block-prices--price-group--cross-article--basket-items-quantity' data-priceGroupId='{$sArticle.pricegroupID}' data-getPriceGroupCartItemsQuantityUrl='{url controller="detail" action="getPriceGroupCartItemsQuantity" _seo=false}'>-</strong>"
                }
                {assign
                    var='priceGroupName'
                    value="<strong id='block-prices--price-group--cross-article--name'>{$sArticle.pricegroupName}</strong>"
                }
                <p class="block-prices--price-group--cross-article">
                    {$priceGroupQuantityParagraph|sprintf:$priceGroupQuantity:$priceGroupName}
                </p>
            {/block}
        {/if}

    </div>
{/block}
