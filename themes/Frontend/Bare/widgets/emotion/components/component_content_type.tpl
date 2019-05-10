{block name="widget_emotion_component_content_type"}
    <div class="emotion--blog">
        {if $Data.sItems}
            {block name="widget_emotion_component_content_type_container"}
                <div class="blog--container block-group">
                    {foreach $Data.sItems as $entry}
                        {$link = {url controller='custom'|cat:$sType.internalName action=detail id=$entry.id}}
                        {$title = $entry[$Data.sType.viewTitleFieldName]}
                        {$image = $entry[$Data.sType.viewImageFieldName]}
                        {$description = $entry[$Data.sType.viewDescriptionFieldName]}

                        {if isset($image[0])}
                            {$image = $image[0]}
                        {/if}

                        {block name="widget_emotion_component_content_type_entry"}
                            <div class="blog--entry blog--entry-{$entry@index} block"
                                 style="width:{{"100" / $Data.sItems|count}|round:2}%">

                                {block name="widget_emotion_component_content_type_entry_image"}
                                    {if $image}
                                        {$images = $image.thumbnails}
                                        {if !isset($images[0])}
                                            {$images[0] = ['source' => $image.source]}
                                            {$images[1] = ['source' => $image.source]}
                                        {/if}

                                        {strip}
                                            <style type="text/css">

                                                #teaser--{$Data.objectId}-{$entry@index} {
                                                    background-image: url('{$images[0].source}');
                                                }

                                                {if isset($images[0].retinaSource)}
                                                @media screen and (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
                                                    #teaser--{$Data.objectId}-{$entry@index} {
                                                        background-image: url('{$images[0].retinaSource}');
                                                    }
                                                }
                                                {/if}

                                                @media screen and (min-width: 48em) {
                                                    #teaser--{$Data.objectId}-{$entry@index} {
                                                        background-image: url('{$images[1].source}');
                                                    }
                                                }

                                                {if isset($images[1].retinaSource)}
                                                @media screen and (min-width: 48em) and (-webkit-min-device-pixel-ratio: 2),
                                                screen and (min-width: 48em) and (min-resolution: 192dpi) {
                                                    #teaser--{$Data.objectId}-{$entry@index} {
                                                        background-image: url('{$images[1].retinaSource}');
                                                    }
                                                }
                                                {/if}

                                                @media screen and (min-width: 78.75em) {
                                                    .is--fullscreen #teaser--{$Data.objectId}-{$entry@index} {
                                                        background-image: url('{$images[2].source}');
                                                    }
                                                }

                                                {if isset($images[2].retinaSource)}
                                                @media screen and (min-width: 78.75em) and (-webkit-min-device-pixel-ratio: 2),
                                                screen and (min-width: 78.75em) and (min-resolution: 192dpi) {
                                                    .is--fullscreen #teaser--{$Data.objectId}-{$entry@index} {
                                                        background-image: url('{$images[2].retinaSource}');
                                                    }
                                                }
                                                {/if}
                                            </style>
                                        {/strip}

                                        <a class="blog--image"
                                           id="teaser--{$Data.objectId}-{$entry@index}"
                                           href="{$link}"
                                           title="{$title|escape}">&nbsp;</a>
                                    {else}
                                        <a class="blog--image"
                                           href="{$link}"
                                           title="{$title|escape}">
                                            {s name="EmotionBlogPreviewNopic"}{/s}
                                        </a>
                                    {/if}
                                {/block}

                                {block name="widget_emotion_component_content_type_entry_title"}
                                    <a class="blog--title"
                                       href="{$link}"
                                       title="{$title|escape}">
                                       {$title|truncate:40}
                                    </a>
                                {/block}

                                {block name="widget_emotion_component_content_type_entry_description"}
                                    <div class="blog--description">
                                        {$description|strip_tags|trim|truncate:135}
                                    </div>
                                {/block}
                            </div>
                        {/block}
                    {/foreach}
                </div>
            {/block}
        {/if}
    </div>
{/block}
