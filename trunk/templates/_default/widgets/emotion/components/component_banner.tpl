<div class="mapping">
    {if $Data.link}
        <a href="{$Data.link}">
            <img src="{link file=$Data.file}" />
        </a>
    {else}
        <img src="{link file=$Data.file}" />
    {/if}
    {if $Data.bannerMapping}
        <div class="banner-mapping" style="height: {$sElementHeight}px;width: {$sElementWidth}px">
        {foreach $Data.bannerMapping as $mapping}
            <a href="{$mapping.link}" class="emotion-banner-mapping" style="width:{$mapping.width}px;height:{$mapping.height}px;left:{$mapping.x}px;top:{$mapping.y}px"></a>
        {/foreach}
        </div>
    {/if}
</div>