{block name="widget_emotion_component_banner"}
    <div class="emotion--banner"
         data-width="{$Data.fileInfo.width}"
         data-height="{$Data.fileInfo.height}"
         {if $Data.bannerMapping}data-bannerMapping="true"{/if}>

        {strip}
        <style type="text/css">
            {if empty($Data.thumbnails)}
                #banner--{$Data.objectId} {
                    background-image: url('{$Data.source}');
                }
            {else}
                {$images = $Data.thumbnails}

                #banner--{$Data.objectId} {
                    background-image: url('{$images[0].source}');
                }

                {if isset($images[0].retinaSource)}
                @media screen and (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
                    #banner--{$Data.objectId} {
                        background-image: url('{$images[0].retinaSource}');
                    }
                }
                {/if}

                @media screen and (min-width: 48em) {
                    #banner--{$Data.objectId} {
                        background-image: url('{$images[1].source}');
                    }
                }

                {if isset($images[1].retinaSource)}
                @media screen and (min-width: 48em) and (-webkit-min-device-pixel-ratio: 2),
                       screen and (min-width: 48em) and (min-resolution: 192dpi) {
                    #banner--{$Data.objectId} {
                        background-image: url('{$images[1].retinaSource}');
                    }
                }
                {/if}

                @media screen and (min-width: 78.75em) {
                    .is--fullscreen #banner--{$Data.objectId} {
                        background-image: url('{$images[2].source}');
                    }
                }

                {if isset($images[2].retinaSource)}
                @media screen and (min-width: 78.75em) and (-webkit-min-device-pixel-ratio: 2),
                       screen and (min-width: 78.75em) and (min-resolution: 192dpi) {
                    .is--fullscreen #banner--{$Data.objectId} {
                        background-image: url('{$images[2].retinaSource}');
                    }
                }
                {/if}
            {/if}
        </style>
        {/strip}

        {block name="widget_emotion_component_banner_inner"}
            <div class="banner--content"
                 id="banner--{$Data.objectId}"
                 {if $Data.bannerPosition}style="background-position: {$Data.bannerPosition}"{/if}>

                {* Banner mapping, based on the same technic as an image map *}
                {block name="widget_emotion_component_banner_mapping"}
                    {if $Data.bannerMapping}
                        <div class="banner--mapping {$Data.bannerPosition}">
                            {foreach $Data.bannerMapping as $mapping}
                                <a href="{$mapping.link}"
                                   class="banner--mapping-link"
                                   style="width:{({$mapping.width} / ({$Data.fileInfo.width} / 100))|round:3}%;
                                          height:{({$mapping.height} / ({$Data.fileInfo.height} / 100))|round:3}%;
                                          left:{({$mapping.x} / ({$Data.fileInfo.width} / 100))|round:3}%;
                                          top:{({$mapping.y} / ({$Data.fileInfo.height} / 100))|round:3}%"
                                   {if $mapping.title} title="{$mapping.title|escape}"{/if}
                                   {if $mapping.linkLocation eq "external"} target="_blank"{/if}>&nbsp;</a>
                            {/foreach}
                        </div>
                    {elseif $Data.link}
                        <a href="{$Data.link}" class="banner--link"></a>
                    {/if}
                {/block}
            </div>
        {/block}
    </div>
{/block}