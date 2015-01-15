{block name='frontend_detail_product_quick_view'}
    <div class="product--quick-view">
        {block name='frontend_detail_product_quick_view_inner'}
            {block name='frontend_detail_product_quick_view_image_link'}
                <a class="quick-view--image-link" href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articlename|escape:"html"}{/if}">
                    {block name='frontend_detail_product_quick_view_image'}
                        <span class="quick-view--image" data-picture="true" data-alt="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articlename|escape:"html"}{/if}">
                            {block name='frontend_detail_product_quick_view_image_inner'}
                                {*Image based on our default media queries*}
                                {block name='product_quick_view_image_default_queries'}
                                    <span data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}"></span>
                                {/block}

                                {*Block to add additional image based on media queries*}
                                {block name='product_quick_view_image_additional_queries'}{/block}

                                {*If the browser doesn't support JS, the following image will be used*}
                                {block name='product_quick_view_image_fallback'}
                                    <noscript>
                                        <img src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" alt="{$sArticle.articleName|escape:"html"}">
                                    </noscript>
                                {/block}
                            {/block}
                        </span>
                    {/block}
                </a>
            {/block}

            {block name='frontend_detail_product_quick_view_header'}
                <div class="quick-view--header">
                    {block name='frontend_detail_product_quick_view_header_inner'}
                        {block name='frontend_detail_product_quick_view_title'}
                            <a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" class="quick-view--title" title="{$sArticle.articleName|escape:"html"}">
                                {block name='frontend_detail_product_quick_view_title_inner'}
                                    {$sArticle.articleName|escape:"html"}
                                {/block}
                            </a>
                        {/block}

                        {block name='frontend_detail_product_quick_view_supplier'}
                            <div class="quick-view--supplier">
                                {block name='frontend_detail_product_quick_view_supplier_inner'}
                                    {$sArticle.supplierName|escape:"html"}
                                {/block}
                            </div>
                        {/block}
                    {/block}
                </div>
            {/block}

            {block name='frontend_detail_product_quick_view_description_title'}
                <div class="quick-view--description-title">
                    {block name='frontend_detail_product_quick_view_description_title_inner'}
                        {s name="DetailDescriptionHeader" namespace="frontend/detail/description"}Produktinformationen{/s}
                    {/block}
                </div>
            {/block}

            {block name='frontend_detail_product_quick_view_description'}
                <div class="quick-view--description">
                    {block name='frontend_detail_product_quick_view_description_inner'}
                        {$sArticle.description_long}
                    {/block}
                </div>
            {/block}
        {/block}
    </div>
{/block}