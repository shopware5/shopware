{extends file="frontend/content_type/field/base.tpl"}

{block name='frontend_content_type_field_base_content'}
    {$description = $content.description|escape|truncate:160}
    {$title = $content.title|escape|truncate:160}

    {block name='frontend_content_type_field_media_link'}
        <a href="{$content.source}"
           data-lightbox="true"
           title="{$title}">

            {block name='frontend_content_type_field_media_image'}
                {if count($content.thumbnails) < 2}
                    <img src="{link file='frontend/_public/src/img/no-picture.jpg'}"
                         alt="{$description}"
                         title="{$title}"/>
                {else}
                    <picture>
                        <source srcset="{$content.thumbnails[1].retinaSource}" media="(min-resolution: 192dpi), (-webkit-min-device-pixel-ratio: 2)">
                        <source srcset="{$content.thumbnails[1].sourceSet}">

                        <img srcset="{$content.thumbnails[1].sourceSet}"
                             alt="{$description}"
                             title="{$title}"/>
                    </picture>
                {/if}
            {/block}

        </a>
    {/block}
{/block}
