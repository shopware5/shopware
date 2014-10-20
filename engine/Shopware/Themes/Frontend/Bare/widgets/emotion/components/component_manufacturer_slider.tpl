{$dataXsConfig = '{ perPage: 1,perSlide: 1, touchControl: true }'}
{$dataMConfig = '{ perPage: 2, perSlide: 1, touchControl: true }'}
{$dataLConfig = '{ perPage: 4, perSlide: 1, touchControl: true }'}
{$dataXlConfig = '{ perPage: 5, perSlide: 1, touchControl: true }'}

{$rowSpan = $element.endRow - $element.startRow + 1}

{block name="frontend_widgets_manufacturer_slider"}
    <div class="panel has--border manufacturer--panel element-height--{$rowSpan}">

        {* Manufacturer title *}
        {block name="frontend_widgets_manufacturer_slider_title"}
            {if $Data.manufacturer_slider_title}
                <div class="panel--title is--underline manufacturer--title">
                    {$Data.manufacturer_slider_title}
                </div>
            {/if}
        {/block}

        {* Manufacturer Content *}
        {block name="frontend_widgets_manufacturer_slider_content"}
            <div class="panel--body is--wide manufacturer--body">
                {block name="frontend_widgets_manufacturer_slider_container"}
                    <div class="product-slider" data-all="productSlider" data-xs-config="{$dataXsConfig}" data-m-config="{$dataMConfig}" data-l-config="{$dataLConfig}" data-xl-config="{$dataXlConfig}">
                        <div class="product-slider--container">
                            {foreach $Data.values as $supplier}
                                {block name="frontend_widgets_manufacturer_slider_item"}
                                    <div class="manufacturer--item product-slider--item">

                                        {block name="frontend_widgets_manufacturer_slider_item_link"}
                                            <a href="{$supplier.link}" title="{$supplier.name|escape:'html'}" class="manufacturer--link{if !$supplier.image} has--text{/if}">
                                                {if $supplier.image}
                                                    {block name="frontend_widgets_manufacturer_slider_item_image"}
                                                        <span class="is--centered"><img class="manufacturer--image" src="{$supplier.image}" alt="{$supplier.name|escape:'html'}" /></span>
                                                    {/block}
                                                {else}
                                                    {block name="frontend_widgets_manufacturer_slider_item_text"}
                                                        <span class="is--centered">{$supplier.name}</span>
                                                    {/block}
                                                {/if}
                                            </a>
                                        {/block}
                                    </div>
                                {/block}
                            {/foreach}
                        </div>
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}