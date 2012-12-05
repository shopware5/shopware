{* todo@stp - add snippets *}
{extends file="parent:frontend/detail/index.tpl"}

{* Always hide the pseudo price *}
{block name='frontend_detail_data_pseudo_price'}
    {if !$aboCommerce}
        <div class="space"></div>
        {$smarty.block.parent}
    {/if}
{/block}

{* Include block prices *}
{block name="frontend_detail_data_block_price_include"}
    {if !$aboCommerce}
        {include file="frontend/detail/block_price.tpl" sArticle=$sArticle}
    {/if}
{/block}

{* Hide the headline of the block prices if abo commerce is enabled *}
{block name='frontend_detail_data_block_prices_headline'}
    {if !$aboCommerce}
        {$smarty.block.parent}
    {/if}
{/block}


{block name='frontend_detail_data_price_configurator'}
    {if !$aboCommerce}
        {$smarty.block.parent}
    {/if}
{/block}

{* Inject the abo commerce selection *}
{block name='frontend_detail_data_price_info' append}
    {if $aboCommerce}

        {* Cache the aboCommerce array *}
        <div class="hidden abocommerce-data">{$aboCommerce|json_encode}</div>
        <div class="hidden block-prices-data">{$sArticle.sBlockPrices|json_encode}</div>

        {* Abo commerce container *}
        {block name="frontend_detail_data_abo_commerce"}
            <div class="abo-commerce-container">

                {* Normal single purchase *}
                {block name="frontend_detail_data_abo_commerce_single"}
                {if !$aboCommerce.isExclusive && !$aboCommerceArticleInBasket}
                    <div class="single-delivery abo-container">
                        <div class="abo-selection">
                            <input id="single-delivery" name="aboSelection" class="selection" value="single" type="radio" checked="checked" />
                        </div>
                        <div class="abo-info">
                            <label for="single-delivery">Einmalige Lieferung</label>
                            {if $sArticle.sBlockPrices}
                                {include file="frontend/detail/block_price.tpl" sArticle=$sArticle}
                            {else}
                                <div class="abo-price">{$sArticle.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</div>
                            {/if}
                        </div>
                        <div class="clear"></div>
                    </div>
                {/if}
                {/block}

                {* Abo purchase *}
                {block name="frontend_detail_data_abo_commerce_abo"}
                    {if !$aboCommerceStandardArticleInBasket}
                    {$aboPrice = $aboCommerce.prices.0}
                    <div class="abo-delivery abo-container">
                        <div class="abo-selection">
                            <input id="abo-delivery" name="aboSelection" class="selection" value="abo" type="radio"{if $aboCommerce.isExclusive || $aboCommerceArticleInBasket} checked="checked"{/if} />
                        </div>

                        <div class="abo-info">
                            <label for="abo-delivery">Sparabo</label>

                            {if $aboCommerce.description}
                                <div class="desc">{$aboCommerce.description|strip_tags|truncate:100}</div>
                            {/if}

                            <div class="abo-price abo-pseudo-price">
                                <strong class="pseudo-price">
                                    <span class="line-through">
                                        {s name="reducedPrice" namespace="frontend/listing/box_article"}{/s} {$sArticle.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                                    </span>
                                    &nbsp;(<span class="percent">{$aboPrice.descountPercentage|replace:'.':','}</span>% gespart)
                                </strong>
                                <span class="price">{$aboPrice.discountPrice|currency}</span> {s name="Star" namespace="frontend/listing/box_article"}{/s}
                            </div>

                            <p class="desc">
                                Lorem ipsum dolor sit <a href="#open-price-separation" class="link-open-price-separation">amet consetetur</a>
                            </p>
                        </div>
                        <div class="clear"></div>
                    </div>
                    {/if}
                {/block}

                {* Interval selection *}
                {block name="frontend_detail_data_abo_commerce_interval"}
                    {if !$aboCommerceStandardArticleInBasket && $aboCommerce}
                        {include file="frontend/detail/interval_select.tpl"}
                    {/if}
                {/block}
            </div>
        {/block}
    {else}
        <div class="clear"></div>
        {$smarty.block.parent}
    {/if}
{/block}

{* Modify the quantity select box *}
{block name='frontend_detail_buy_quantity'}
    {if $aboCommerce}
        {include file="frontend/detail/quantity_select.tpl"}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* Remove notepad link *}
{block name='frontend_detail_actions_notepad'}
    {if !$aboCommerceOrderListsActive}
        {$smarty.block.parent}
    {/if}
{/block}

{* Add the "Add to orderlist" link *}
{block name='frontend_detail_actions_voucher' append}
<li class="lastrow">
    <a href="#open-orderlist-popup" class="open-orderlist-popup" rel="nofollow" title="Artikel auf Bestellliste setzen" data-ordernumber="{$sArticle.ordernumber}">
        Artikel auf Bestellliste setzen
    </a>
</li>
{/block}

{* Add orderlist popup *}
{block name='frontend_index_body_inline' append}
    {include file="frontend/detail/price_separation_popup.tpl"}
    {if $aboCommerceOrderListsActive}
        {include file="frontend/detail/orderlist_popup.tpl"}
    {/if}
{/block}