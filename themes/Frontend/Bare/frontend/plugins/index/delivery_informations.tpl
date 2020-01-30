{* Delivery informations *}
{block name='frontend_widgets_delivery_infos'}
    <div class="product--delivery">
        {block name='frontend_widgets_delivery_infos_inner'}
            {if $sArticle.shippingfree}
                {block name='frontend_widgets_delivery_infos_shippingfree'}
                    <p class="delivery--information">
                        <span class="delivery--text delivery--text-shipping-free">
                            <i class="delivery--status-icon delivery--status-shipping-free"></i>
                            {s name="DetailDataInfoShippingfree"}{/s}
                        </span>
                    </p>
                {/block}
            {/if}
            {if isset($sArticle.active) && !$sArticle.active}
                {block name='frontend_widgets_delivery_infos_not_active'}
                    <link itemprop="availability" href="https://schema.org/LimitedAvailability" />
                    <p class="delivery--information">
                        <span class="delivery--text  delivery--text-not-available">
                            <i class="delivery--status-icon delivery--status-not-available"></i>
                            {s name="DetailDataInfoNotAvailable"}{/s}
                        </span>
                    </p>
                {/block}
            {elseif $sArticle.sReleaseDate && $sArticle.sReleaseDate|date_format:"%Y%m%d" > $smarty.now|date_format:"%Y%m%d"}
                {block name='frontend_widgets_delivery_infos_preorder'}
                    <link itemprop="availability" href="https://schema.org/PreOrder" />
                    <p class="delivery--information">
                        <span class="delivery--text delivery--text-more-is-coming">
                            <i class="delivery--status-icon delivery--status-more-is-coming"></i>
                            {s name="DetailDataInfoShipping"}{/s} {$sArticle.sReleaseDate|date:'date_long'}
                        </span>
                    </p>
                {/block}
            {elseif $sArticle.esd}
                {block name='frontend_widgets_delivery_infos_download'}
                    <link itemprop="availability" href="https://schema.org/InStock" />
                    <p class="delivery--information">
                        <span class="delivery--text delivery--text-available">
                            <i class="delivery--status-icon delivery--status-available"></i>
                            {s name="DetailDataInfoInstantDownload"}{/s}
                        </span>
                    </p>
                {/block}
            {elseif {config name="instockinfo"} && $sArticle.modus == 0 && $sArticle.instock > 0 && $sArticle.quantity > $sArticle.instock}
                {block name='frontend_widgets_delivery_infos_partial'}
                    <link itemprop="availability" href="https://schema.org/LimitedAvailability" />
                    <p class="delivery--information">
                        <span class="delivery--text delivery--text-more-is-coming">
                            <i class="delivery--status-icon delivery--status-more-is-coming"></i>
                            {s name="DetailDataInfoPartialStock"}{/s}
                        </span>
                    </p>
                {/block}
            {elseif $sArticle.instock >= $sArticle.minpurchase}
                {block name='frontend_widgets_delivery_infos_instock'}
                    <link itemprop="availability" href="https://schema.org/InStock" />
                    <p class="delivery--information">
                        <span class="delivery--text delivery--text-available">
                            <i class="delivery--status-icon delivery--status-available"></i>
                            {s name="DetailDataInfoInstock"}{/s}
                        </span>
                    </p>
                {/block}
            {elseif $sArticle.shippingtime}
                {block name='frontend_widgets_delivery_infos_future_shipping'}
                    <link itemprop="availability" href="https://schema.org/LimitedAvailability" />
                    <p class="delivery--information">
                        <span class="delivery--text delivery--text-more-is-coming">
                            <i class="delivery--status-icon delivery--status-more-is-coming"></i>
                            {s name="DetailDataShippingtime"}{/s} {$sArticle.shippingtime} {s name="DetailDataShippingDays"}{/s}
                        </span>
                    </p>
                {/block}
            {else}
                {block name='frontend_widgets_delivery_infos_not_available'}
                    <link itemprop="availability" href="https://schema.org/LimitedAvailability" />
                    <p class="delivery--information">
                        <span class="delivery--text delivery--text-not-available">
                            <i class="delivery--status-icon delivery--status-not-available"></i>
                            {s name="DetailDataNotAvailable"}{config name=notavailable}{/s}
                        </span>
                    </p>
                {/block}
            {/if}
        {/block}
    </div>
{/block}
