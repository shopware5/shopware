{block name="widget_emotion_component_banner"}
    <div class="emotion--banner"
         data-width="{$Data.fileInfo.width}"
         data-height="{$Data.fileInfo.height}"
         {if $Data.bannerMapping}data-bannerMapping="true"{/if}>

        {block name="widget_emotion_component_banner_inner"}
            <div class="banner--content"
                 style="background-image: url('{link file=$Data.file}');{if $Data.bannerPosition} background-position: {$Data.bannerPosition}{/if}">

                {* Banner mapping, based on the same technic as an image map *}
                {block name="widget_emotion_component_banner_mapping"}
                    {if $Data.bannerMapping}
                        <div class="banner--mapping">
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