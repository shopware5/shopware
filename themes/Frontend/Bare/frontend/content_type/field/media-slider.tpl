{block name='frontend_content_type_images_thumbnails'}
    <div class="content-type--media-slider">
        {foreach $content as $sArticleMedia}

            {$alt = $sArticle.title|escape}

            {if $sArticleMedia.description}
                {$alt = $sArticleMedia.description|escape}
            {/if}

            {if !$sArticleMedia.preview}
                <a href="{$sArticleMedia.source}"
                   data-lightbox="true"
                   class="content-type--media-slider-item"
                   title="{$alt}">

                    {if $sArticleMedia.thumbnails[0].sourceSet}
                        <img srcset="{$sArticleMedia.thumbnails[0].sourceSet}"
                             class="blog--thumbnail-image"
                             alt="{$alt}"
                             title="{$alt|truncate:160}" />
                    {else}
                        <img srcset="{media path=$sArticleMedia.media.path}"
                             class="blog--thumbnail-image"
                             alt="{$alt}"
                             title="{$alt|truncate:160}" />
                    {/if}
                </a>
            {/if}
        {/foreach}
    </div>
{/block}
