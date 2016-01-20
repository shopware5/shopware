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
                        {$colSize = 100 / $emotion.grid.cols}
                        {$itemSize = $itemCols * $colSize}

                        {foreach $Data.thumbnails as $image}
                            {$srcSet = "{if $image@index !== 0}{$srcSet}, {/if}{$image.source} {$image.maxWidth}w"}

                            {if $image.retinaSource}
                                {$srcSetRetina = "{if $image@index !== 0}{$srcSetRetina}, {/if}{$image.retinaSource} {$image.maxWidth}w"}
                            {/if}
                        {/foreach}
                    {else}
                        {$baseSource = $Data.source}
                    {/if}

                    <picture>
                        {if $srcSetRetina}<source sizes="{$itemSize}vw" srcset="{$srcSetRetina}" media="(min-resolution: 192dpi)" />{/if}
                        {if $srcSet}<source sizes="{$itemSize}vw" srcset="{$srcSet}" />{/if}
                        <img src="{$baseSource}" sizes="{$itemSize}vw" class="banner--image"{if $Data.title} alt="{$Data.title|escape}"{/if} />
                    </picture>
                {/block}

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