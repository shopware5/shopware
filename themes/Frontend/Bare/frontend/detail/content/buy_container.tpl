{block name='frontend_detail_index_buy_container'}
    <div class="product--buybox block{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2} is--wide{/if}">

        {block name="frontend_detail_rich_snippets_brand"}
            <meta itemprop="brand" content="{$sArticle.supplierName|escape}"/>
        {/block}

        {block name="frontend_detail_rich_snippets_weight"}
            {if $sArticle.weight}
                <meta itemprop="weight" content="{$sArticle.weight} kg"/>
            {/if}
        {/block}

        {block name="frontend_detail_rich_snippets_height"}
            {if $sArticle.height}
                <meta itemprop="height" content="{$sArticle.height} cm"/>
            {/if}
        {/block}

        {block name="frontend_detail_rich_snippets_width"}
            {if $sArticle.width}
                <meta itemprop="width" content="{$sArticle.width} cm"/>
            {/if}
        {/block}

        {block name="frontend_detail_rich_snippets_depth"}
            {if $sArticle.length}
                <meta itemprop="depth" content="{$sArticle.length} cm"/>
            {/if}
        {/block}

        {block name="frontend_detail_rich_snippets_release_date"}
            {if $sArticle.sReleasedate}
                <meta itemprop="releaseDate" content="{$sArticle.sReleasedate}"/>
            {/if}
        {/block}

        {block name='frontend_detail_buy_laststock'}
            {s name="DetailBuyInfoNotAvailable" namespace="frontend/detail/buy" assign="snippetDetailBuyInfoNotAvailable"}{/s}
            {if !$sArticle.isAvailable && !$sArticle.sConfigurator}
                {include file="frontend/_includes/messages.tpl" type="error" content=$snippetDetailBuyInfoNotAvailable}
            {elseif !$sArticle.isAvailable && $sArticle.isSelectionSpecified}
                {include file="frontend/_includes/messages.tpl" type="error" content=$snippetDetailBuyInfoNotAvailable}
            {elseif !$sArticle.isAvailable && !$sArticle.hasAvailableVariant}
                {include file="frontend/_includes/messages.tpl" type="error" content=$snippetDetailBuyInfoNotAvailable}
            {/if}
        {/block}

        {* Product email notification *}
        {block name="frontend_detail_index_notification"}
            {if $ShowNotification && $sArticle.notification && $sArticle.instock < $sArticle.minpurchase}
                {* Support products with or without variants *}
                {if ($sArticle.hasAvailableVariant && ($sArticle.isSelectionSpecified || !$sArticle.sConfigurator)) || !$sArticle.hasAvailableVariant}
                    {include file="frontend/plugins/notification/index.tpl"}
                {/if}
            {/if}
        {/block}

        {* Product data *}
        {block name='frontend_detail_index_buy_container_inner'}
            <div itemprop="offers" itemscope itemtype="{if $sArticle.sBlockPrices}http://schema.org/AggregateOffer{else}http://schema.org/Offer{/if}" class="buybox--inner">

                {block name='frontend_detail_index_data'}
                    {if $sArticle.sBlockPrices}
                        {$lowestPrice=false}
                        {$highestPrice=false}
                        {foreach $sArticle.sBlockPrices as $blockPrice}
                            {if $lowestPrice === false || $blockPrice.price < $lowestPrice}
                                {$lowestPrice=$blockPrice.price}
                            {/if}
                            {if $highestPrice === false || $blockPrice.price > $highestPrice}
                                {$highestPrice=$blockPrice.price}
                            {/if}
                        {/foreach}
                        <meta itemprop="lowPrice" content="{$lowestPrice}"/>
                        <meta itemprop="highPrice" content="{$highestPrice}"/>
                        <meta itemprop="offerCount" content="{$sArticle.sBlockPrices|count}"/>
                    {/if}

                    {block name="frontend_detail_index_data_price_currency"}
                        <meta itemprop="priceCurrency" content="{$Shop->getCurrency()->getCurrency()}"/>
                    {/block}

                    {block name="frontend_detail_index_data_price_valid_until"}{/block}

                    {block name="frontend_detail_index_data_url"}
                        <meta itemprop="url" content="{url sArticle=$sArticle.articleID title=$sArticle.articleName}"/>
                    {/block}

                    {include file="frontend/detail/data.tpl" sArticle=$sArticle sView=1}
                {/block}

                {block name='frontend_detail_index_after_data'}{/block}

                {* Configurator drop down menus *}
                {block name="frontend_detail_index_configurator"}
                    <div class="product--configurator">
                        {if $sArticle.sConfigurator}
                            {if $sArticle.sConfiguratorSettings.type == 1}
                                {$file = 'frontend/detail/config_step.tpl'}
                            {elseif $sArticle.sConfiguratorSettings.type == 2}
                                {$file = 'frontend/detail/config_variant.tpl'}
                            {else}
                                {$file = 'frontend/detail/config_upprice.tpl'}
                            {/if}
                            {include file=$file}
                        {/if}
                    </div>
                {/block}

                {* Include buy button and quantity box *}
                {block name="frontend_detail_index_buybox"}
                    {include file="frontend/detail/buy.tpl"}
                {/block}

                {* Product actions *}
                {block name="frontend_detail_index_actions"}
                    <nav class="product--actions">
                        {include file="frontend/detail/actions.tpl"}
                    </nav>
                {/block}
            </div>
        {/block}

        {* Product - Base information *}
        {block name='frontend_detail_index_buy_container_base_info'}
            <ul class="product--base-info list--unstyled">

                {* Product SKU *}
                {block name='frontend_detail_data_ordernumber'}
                    <li class="base-info--entry entry--sku">

                        {* Product SKU - Label *}
                        {block name='frontend_detail_data_ordernumber_label'}
                            <strong class="entry--label">
                                {s name="DetailDataId" namespace="frontend/detail/data"}{/s}
                            </strong>
                        {/block}

                        {* Product SKU - Content *}
                        {block name='frontend_detail_data_ordernumber_content'}
                            <meta itemprop="productID" content="{$sArticle.articleDetailsID}"/>
                            <span class="entry--content" itemprop="sku">
                                {$sArticle.ordernumber}
                            </span>
                        {/block}
                    </li>
                {/block}

                {* Product attributes fields *}
                {block name='frontend_detail_data_attributes'}

                    {* Product attribute 1 *}
                    {block name='frontend_detail_data_attributes_attr1'}
                        {if $sArticle.attr1}
                            <li class="base-info--entry entry-attribute">
                                <strong class="entry--label">
                                    {s name="DetailAttributeField1Label" namespace="frontend/detail/index"}{/s}:
                                </strong>

                                <span class="entry--content">
                                    {$sArticle.attr1|escape}
                                </span>
                            </li>
                        {/if}
                    {/block}

                    {* Product attribute 2 *}
                    {block name='frontend_detail_data_attributes_attr2'}
                        {if $sArticle.attr2}
                            <li class="base-info--entry entry-attribute">
                                <strong class="entry--label">
                                    {s name="DetailAttributeField2Label" namespace="frontend/detail/index"}{/s}:
                                </strong>

                                <span class="entry--content">
                                    {$sArticle.attr2|escape}
                                </span>
                            </li>
                        {/if}
                    {/block}
                {/block}
            </ul>
        {/block}
    </div>
{/block}
