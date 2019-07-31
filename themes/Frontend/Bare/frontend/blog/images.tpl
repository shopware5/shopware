{* Article picture *}
{if $sArticle.media}
    <div class="blog--detail-image-container block">

        {* Main Image *}
        {block name='frontend_blog_images_main_image'}
            {$alt = $sArticle.title|escape}

            {if $sArticle.preview.description}
                {$alt = $sArticle.preview.description|escape}
            {/if}

            <div class="blog--detail-images block">
                <a href="{$sArticle.preview.source}"
                   data-lightbox="true"
                   {if $sArticle.preview.extension === 'svg'}
                   data-is-svg='true'
                   {/if}
                   title="{$alt}"
                   class="link--blog-image">

                    {if $sArticle.preview.thumbnails[1].sourceSet}
                        <img srcset="{$sArticle.preview.thumbnails[1].sourceSet}"
                              src="{$sArticle.preview.thumbnails[1].source}"
                              class="blog--image panel has--border is--rounded"
                              alt="{$alt}"
                              title="{$alt|truncate:160}"/>
                    {else}
                        <img srcset="{media path=$sArticle.media[0].media.path}"
                             src="{media path=$sArticle.media[0].media.path}"
                             class="blog--image panel has--border is--rounded"
                             alt="{$alt}"
                             title="{$alt|truncate:160}"/>
                    {/if}
                </a>
            </div>
        {/block}

        {* Thumbnails *}
        {block name='frontend_blog_images_thumbnails'}
            <div class="blog--detail-thumbnails block">
                {foreach $sArticle.media as $sArticleMedia}

                    {$alt = $sArticle.title|escape}

                    {if $sArticleMedia.description}
                        {$alt = $sArticleMedia.description}
                    {/if}

                    {if !$sArticleMedia.preview}
                        <a href="{$sArticleMedia.source}"
                           data-lightbox="true"
                           class="blog--thumbnail panel has--border is--rounded block"
                           title="{s name="BlogThumbnailText" namespace="frontend/blog/detail"}{/s}: {$alt}">

                            {if $sArticleMedia.thumbnails[0].sourceSet}
                                <img srcset="{$sArticleMedia.thumbnails[0].sourceSet}"
                                     class="blog--thumbnail-image"
                                     alt="{s name="BlogThumbnailText" namespace="frontend/blog/detail"}{/s}: {$alt}"
                                     title="{s name="BlogThumbnailText" namespace="frontend/blog/detail"}{/s}: {$alt|truncate:160}" />
                            {else}
                                <img srcset="{media path=$sArticleMedia.media.path}"
                                     class="blog--thumbnail-image"
                                     alt="{s name="BlogThumbnailText" namespace="frontend/blog/detail"}{/s}: {$alt}"
                                     title="{s name="BlogThumbnailText" namespace="frontend/blog/detail"}{/s}: {$alt|truncate:160}" />
                           {/if}
                        </a>
                    {/if}
                {/foreach}
            </div>
        {/block}
    </div>
{/if}
