<div class="product--quick-view">
    <a class="quick-view--image-link" href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}">
        <span class="quick-view--image" data-picture="true" data-alt="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articlename|escape:"html"}{/if}">
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
        </span>
    </a>

    <div class="quick-view--header">
        <a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" class="quick-view--title">
            {$sArticle.articleName|escape:"html"}
        </a>

        <div class="quick-view--supplier">
            {$sArticle.supplierName|escape:"html"}
        </div>
    </div>

    <div class="quick-view--description-title">
        {s name="DetailDescriptionHeader" namespace="frontend/detail/description"}Produktinformationen{/s}
    </div>

    <div class="quick-view--description">
        {$sArticle.description_long}
    </div>
</div>