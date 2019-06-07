{block name="widget_emotion_component_banner"}
    <div class="emotion--banner"
         data-coverImage="true"
         data-width="{$Data.fileInfo.width}"
         data-height="{$Data.fileInfo.height}"
         {if $Data.bannerMapping}data-bannerMapping="true"{/if}>

        {block name="widget_emotion_component_banner_inner"}
            <div class="banner--content {$Data.bannerPosition}">

                {block name="widget_emotion_component_banner_image"}

                    {if $Data.thumbnails}
                        {$baseSource = $Data.thumbnails[0].source}
                        {$retinaBaseSource = $Data.thumbnails[0].retinaSource}

                        {foreach $element.viewports as $viewport}
                            {$cols = ($viewport.endCol - $viewport.startCol) + 1}
                            {$elementSize = $cols * $cellWidth}
                            {$size = "{$elementSize}vw"}

                            {if $breakpoints[$viewport.alias]}

                                {if $viewport.alias === 'xl' && !$emotionFullscreen}
                                    {$size = "calc({$elementSize / 100} * {$baseWidth}px)"}
                                {/if}

                                {$size = "(min-width: {$breakpoints[$viewport.alias]}) {$size}"}
                            {/if}

                            {$itemSize = "{$size}{if $itemSize}, {$itemSize}{/if}"}
                        {/foreach}

                        {foreach $Data.thumbnails as $image}
                            {$srcSet = "{if $srcSet}{$srcSet}, {/if}{$image.source} {$image.maxWidth}w"}

                            {if $image.retinaSource}
                                {$retinaSrcSet = "{if $retinaSrcSet}{$retinaSrcSet}, {/if}{$image.retinaSource} {$image.maxWidth}w"}
                            {/if}
                        {/foreach}
                    {else}
                        {$baseSource = $Data.source}
                    {/if}

                    {if $Data.title}
                        {$altText = $Data.title}
                    {else}
                        {$altText = $Data.file}
                    {/if}

                    <picture class="banner--image">
                        <source sizes="{$itemSize}" srcset="{$retinaSrcSet}" media="(min-resolution: 192dpi), (-webkit-min-device-pixel-ratio: 2)">
                        <source sizes="{$itemSize}" srcset="{$srcSet}">

                        {* Fallback *}
                        <img src="{$baseSource}" {if $retinaBaseSource}srcset="{$retinaBaseSource} 2x"{/if} class="banner--image-src" alt="{$altText|escape}" />
                    </picture>
                {/block}

                {* Banner mapping, based on the same technic as an image map *}
                {block name="widget_emotion_component_banner_mapping"}
                    {if $Data.bannerMapping}
                        <div class="banner--mapping {$Data.bannerPosition}">
                            {foreach $Data.bannerMapping as $mapping}
                                <a href="{$mapping.link}"
                                   class="banner--mapping-link"
                                   aria-label="{$mapping.title|escape}"
                                   style="width:{({$mapping.width} / ({$Data.fileInfo.width} / 100))|round:3}%;
                                          height:{({$mapping.height} / ({$Data.fileInfo.height} / 100))|round:3}%;
                                          left:{({$mapping.x} / ({$Data.fileInfo.width} / 100))|round:3}%;
                                          top:{({$mapping.y} / ({$Data.fileInfo.height} / 100))|round:3}%"
                                   {if $mapping.as_tooltip && $mapping.title} title="{$mapping.title|escape}"{/if}
                                   {if $mapping.linkLocation eq "external"} target="_blank"{/if}>&nbsp;</a>
                            {/foreach}
                        </div>
                    {elseif $Data.link}
                        <a href="{$Data.link}" class="banner--link"
                           {if $Data.banner_link_target} target="{$Data.banner_link_target}"{/if}
                           {if $Data.title} title="{$Data.title|escape}"{/if}>
                        </a>
                    {/if}
                {/block}
            </div>
        {/block}
    </div>
{/block}

