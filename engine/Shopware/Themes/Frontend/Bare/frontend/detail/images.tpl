{* Thumbnails *}

{* Thumbnail - Container *}
<div class="image--thumbnails{if !$sArticle.images} has--no-thumbnails{/if}" data-thumbnails="true">

    {* Thumb - Main image *}
    {block name='frontend_detail_image_thumbs_main'}

        {if $sArticle.image.src.5}
            <a href="{$sArticle.image.src.5}"
               class="thumbnail--link is--active"
               title="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}"
               data-xlarge-img="{$sArticle.image.src.5}"
               data-small-img="{$sArticle.image.src.2}"
               data-original-img="{$sArticle.image.src.original}" {block name='frontend_detail_image_thumbs_additional_queries'}{/block}>

                {block name='frontend_detail_image_thumbs_main_img'}
                    <img class="thumbnail--image" src="{$sArticle.image.src.1}" alt="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}">
                {/block}
            </a>
        {/if}

        {if $sArticle.images}
            {* Loop through available images *}
            {foreach $sArticle.images as $image}
                {block name='frontend_detail_image_thumbs_images'}
                <a href="{$image.src.5}"
                   class="thumbnail--link"
                   title="{if $image.res.description}{$image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}"
                   data-xlarge-img="{$image.src.5}"
                   data-small-img="{$image.src.2}"
                   data-original-img="{$image.src.original}" {block name='frontend_detail_image_thumbs_images_additional_queries'}{/block}>

                    {block name='frontend_detail_image_thumbs_images_img'}
                        <img class="thumbnail--image" src="{$image.src.1}" alt="{if $image.res.description}{$image.res.description}{else}{$sArticle.articleName}{/if}">
                    {/block}
                </a>
                {/block}
            {/foreach}
        {/if}
    {/block}

    <div class="thumbnails--arrow thumbnails--trigger">
        <i class="icon--arrow-right"></i>
    </div>
</div>