{* <div class="mapping">
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
				<a href="{$mapping.link}"{if $mapping.linkLocation eq "external"} target="_blank"{/if} class="emotion-banner-mapping" style="width:{$mapping.width}px;height:{$mapping.height}px;left:{$mapping.x}px;top:{$mapping.y}px"{if $mapping.title} title="{$mapping.title}"{/if}></a>
				{if $mapping.as_tooltip === 1 && $mapping.title}
					<div class="banner-mapping-tooltip" style="width:{$mapping.width}px;left:{$mapping.x}px;top:{$mapping.y + $mapping.height - ($mapping.height / 2)}px">
						<span>{$mapping.title}</span>
					</div>
				{/if}
			{/foreach}
        </div>
    {/if}
</div> *}
<div class="emotion--element-banner" style="background-image:url({link file=$Data.file})" data-image-src="{link file=$Data.file}" data-width="{$Data.fileInfo.width}" data-height="{$Data.fileInfo.height}">

	{* Banner link - will be stretched to the full size of the element *}
	{if $Data.link}
		<a class="element-banner--link" href="{$Data.link}">&nbsp;</a>
	{/if}

	{* Banner mapping, similar to a image map *}
	{if $Data.bannerMapping}
		{foreach $Data.bannerMapping as $mapping}
			<a href="{$mapping.link}"{if $mapping.linkLocation eq "external"} target="_blank"{/if} class="element-banner--mapping" style="width:{$mapping.width}px;height:{$mapping.height}px;left:{$mapping.x}px;top:{$mapping.y}px"{if $mapping.title} title="{$mapping.title}"{/if}></a>
			</a>
		{/foreach}
	{/if}
</div>