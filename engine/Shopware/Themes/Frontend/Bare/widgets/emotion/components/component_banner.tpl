{block name="widget_emotion_component_banner_outer"}
<div class="emotion--element-banner-outer">
    {block name="widget_emotion_component_banner_inner"}
        <div class="emotion--element-banner" data-banner-mapping="true" data-image-src="{link file=$Data.file}" data-width="{$Data.fileInfo.width}" data-height="{$Data.fileInfo.height}">

            {* Banner link - will be stretched to the full size of the element *}
            {if $Data.link}
                {block name="widget_emotion_component_banner_link"}
                    <a class="emotion--element-link" href="{$Data.link}">
                        <img class="emotion--element-image" src="{link file=$Data.file}">
                    </a>
                {/block}
            {else}
                {block name="widget_emotion_component_banner_only"}
                    <img class="emotion--element-image" src="{link file=$Data.file}">
                {/block}
            {/if}

            {* Banner mapping, similar to a image map *}
            {block name="widget_emotion_component_banner_mapping"}
                {if $Data.bannerMapping}
                    {foreach $Data.bannerMapping as $mapping}
                        <a href="{$mapping.link}"
                           class="emotion--element-mapping"
                           style="width:{({$mapping.width} / ({$Data.fileInfo.width} / 100))|round:3}%;height:{({$mapping.height} / ({$Data.fileInfo.height} / 100))|round:3}%;left:{({$mapping.x} / ({$Data.fileInfo.width} / 100))|round:3}%;top:{({$mapping.y} / ({$Data.fileInfo.height} / 100))|round:3}%"
                           {if $mapping.title} title="{$mapping.title|escape}"{/if}
                           {if $mapping.linkLocation eq "external"} target="_blank"{/if}>&nbsp;</a>
                    {/foreach}
                {/if}
            {/block}
        </div>
    {/block}
</div>
{/block}