{block name='frontend_content_type_detail_head'}
    {$imageDescription = $image.description|escape|truncate:160}
    {$largeThumbnail = null}

    {if count($image.thumbnails) > 0}
        {$largeThumbnail = $image.thumbnails[count($image.thumbnails) - 1]}
    {/if}

    {block name='frontend_content_type_detail_head_panel'}

        <div class="content-type--head panel">
            {block name='frontend_content_type_detail_head_headline'}
                <h1 class="panel--title is--underline">{$title}</h1>
            {/block}

            {block name='frontend_content_type_detail_head_body'}
                <div class="panel--body">
                    {block name='frontend_content_type_detail_head_image'}
                        <div class="head--picture">
                            {if $largeThumbnail === null}
                                <img src="{link file='frontend/_public/src/img/no-picture.jpg'}"
                                     alt="{$imageDescription}"
                                     title="{$imageDescription}" />
                            {else}
                                <picture>
                                    <source srcset="{$largeThumbnail.retinaSource}" media="(min-resolution: 192dpi), (-webkit-min-device-pixel-ratio: 2)">
                                    <source srcset="{$largeThumbnail.sourceSet}">

                                    <img srcset="{$largeThumbnail.sourceSet}"
                                         alt="{$imageDescription}"
                                         title="{$imageDescription}"/>
                                </picture>
                            {/if}
                        </div>
                    {/block}
                    {block name='frontend_content_type_detail_head_description'}
                        <p>{$description}</p>
                    {/block}
                </div>
            {/block}
        </div>

    {/block}
{/block}