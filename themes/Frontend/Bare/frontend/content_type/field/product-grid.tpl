{extends file="frontend/content_type/field/base.tpl"}

{block name='frontend_content_type_field_base_content'}
    <div class="product-slider" data-product-slider="true">
        <div class="product-slider--container is--horizontal">
            {block name='frontend_content_type_field_productgrid_slider_items'}

                {foreach $content as $item}
                    <div class="product-slider--item">
                        {block name='frontend_content_type_field_productgrid_slider_item'}

                            {$item.description_long={$item.description_long|strip_tags|truncate:100}}
                            {$item.description={$item.description|strip_tags|truncate:100}}

                            {include file="frontend/listing/product-box/box-basic.tpl" productBoxLayout='content-type' sArticle=$item}

                        {/block}
                    </div>
                {/foreach}

            {/block}
        </div>
    </div>
{/block}
