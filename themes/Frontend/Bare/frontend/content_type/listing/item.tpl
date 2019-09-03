<div class="blog--box panel has--border is--rounded">
    {block name='frontend_content_type_col_blog_entry'}

        {* Blog Header *}
        {block name='frontend_content_type-col_box_header'}
            <div class="blog--box-header">

                {* Article name *}
                {block name='frontend_content_type_col_article_name'}
                    <h2 class="blog--box-headline panel--title">
                        <a class="blog--box-link" href="{url action=detail id=$sItem.id}" title="{$sItem[$sTitleKey]|escape}">{$sItem[$sTitleKey]}</a>
                    </h2>
                {/block}

                {* Meta data *}
                {block name='frontend_content_type_col_meta_data'}
                    <div class="blog--box-metadata">

                        {* Date *}
                        {block name='frontend_content_type_col_meta_data_date'}
                            <span class="blog--metadata-date blog--metadata is--nowrap is--first">{$sItem.created_at|date:"DATETIME_SHORT"}</span>
                        {/block}
                    </div>
                {/block}

            </div>
        {/block}

        {* Blog Box *}
        {block name='frontend_content_type_col_box_content'}
            <div class="blog--box-content panel--body is--wide block">

                {* Article pictures *}
                {block name='frontend_content_type_col_article_picture'}
                    {if $sItem[$sImageKey]}
                        {$image = $sItem[$sImageKey]}
                        {if $image[0]}
                            {* Image-Slider, take first image *}
                            {$image = $image[0]}
                        {/if}


                        <div class="blog--box-picture">
                            <a href="{url action=detail id=$sItem.id}"
                               class="blog--picture-main"
                               title="{$sItem[$sTitleKey]|escape}">
                                {if !empty($image.thumbnails)}
                                    <img srcset="{$image.thumbnails[0].sourceSet}"
                                         alt="{$sItem[$sTitleKey]|escape}"
                                         title="{$sItem[$sTitleKey]|escape|truncate:160}" />
                                {else}
                                    <img src="{$image.source}"
                                         alt="{$sItem[$sTitleKey]|escape}"
                                         title="{$sItem[$sTitleKey]|escape|truncate:160}" />
                                {/if}
                            </a>
                        </div>
                    {/if}
                {/block}

                {* Article Description *}
                {block name='frontend_content_type_col_description'}
                    <div class="blog--box-description{if !$sItem[$sImageKey]} is--fluid{/if}">

                        {block name='frontend_content_type_col_description_short'}
                            <div class="blog--box-description-short">
                                {$sItem[$sDescriptionKey]|strip_tags|truncate:160:'...'}
                            </div>
                        {/block}

                        {* Read more button *}
                        {block name='frontend_content_type_col_read_more'}
                            <div class="blog--box-readmore">
                                <a href="{url action=detail id=$sItem.id}" title="{$sItem[$sTitleKey]|escape}" class="btn is--primary is--small">{s name="ReadMore" namespace="frontend/content_type/index"}{/s}</a>
                            </div>
                        {/block}
                    </div>
                {/block}

            </div>
        {/block}

    {/block}
</div>
