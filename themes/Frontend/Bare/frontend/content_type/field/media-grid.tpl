{extends file="frontend/content_type/field/base.tpl"}

{block name='frontend_content_type_field_base_content'}
    {block name='frontend_content_type_field_mediagrid_gallery'}
        <div class="image-slider image--gallery"
             data-image-slider="true"
             data-image-gallery="true"
             data-maxZoom="{$theme.lightboxZoomFactor}"
             data-loopSlides="true">

            {block name='frontend_content_type_field_mediagrid_slider'}
                <div class="image-slider--container">
                    <div class="image-slider--slide">
                        {block name='frontend_content_type_field_mediagrid_slider_slide'}

                            {foreach $content as $image}
                                {$description = $image.description|escape|truncate:160}
                                {$largeThumbnail = null}

                                {if count($image.thumbnails) > 0}
                                    {$largeThumbnail = $image.thumbnails[count($image.thumbnails) - 1]}
                                {/if}
                                <div class="image-slider--item">
                                    {block name='frontend_content_type_field_mediagrid_slider_item'}
                                        {if $largeThumbnail === null}
                                            <img src="{link file='frontend/_public/src/img/no-picture.jpg'}"
                                                 alt="{$description}"
                                                 title="{$description}"/>
                                        {else}
                                            <picture>
                                                <source srcset="{$largeThumbnail.retinaSource}" media="(min-resolution: 192dpi), (-webkit-min-device-pixel-ratio: 2)">
                                                <source srcset="{$largeThumbnail.sourceSet}">

                                                <img srcset="{$largeThumbnail.sourceSet}"
                                                     alt="{$description}"
                                                     title="{$description}"/>
                                            </picture>
                                        {/if}
                                    {/block}
                                </div>
                            {/foreach}

                        {/block}
                    </div>
                </div>
            {/block}

            {block name='frontend_content_type_field_mediagrid_thumbnail_slider'}
                <div class="image-slider--thumbnails">
                    <div class="image-slider--thumbnails-slide">
                        {block name='frontend_content_type_field_mediagrid_thumbnail_slider_slide'}

                            {foreach $content as $image}
                                {$description = $image.description|escape|truncate:160}

                                <div class="thumbnail--link">
                                    {block name='frontend_content_type_field_mediagrid_thumbnail_slider_item'}
                                        {if count($image.thumbnails) < 1}
                                            <img src="{link file='frontend/_public/src/img/no-picture.jpg'}"
                                                 alt="{$description}"
                                                 title="{$description}"/>
                                        {else}
                                            <picture>
                                                <source srcset="{$image.thumbnails[0].retinaSource}" media="(min-resolution: 192dpi), (-webkit-min-device-pixel-ratio: 2)">
                                                <source srcset="{$image.thumbnails[0].sourceSet}">

                                                <img srcset="{$image.thumbnails[0].sourceSet}"
                                                     alt="{$description}"
                                                     title="{$description}"/>
                                            </picture>
                                        {/if}
                                    {/block}
                                </div>
                            {/foreach}

                        {/block}
                    </div>
                </div>
            {/block}

        </div>
    {/block}
{/block}