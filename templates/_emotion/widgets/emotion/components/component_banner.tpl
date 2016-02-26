
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
				<a href="{$mapping.link}"{if $mapping.linkLocation eq "external"} target="_blank"{/if} class="emotion-banner-mapping" style="width:{$mapping.width}px;height:{$mapping.height}px;left:{$mapping.x}px;top:{$mapping.y}px"{if $mapping.title} title="{$mapping.title}"{/if}></a>
				{if $mapping.as_tooltip === 1 && $mapping.title}
					<div class="banner-mapping-tooltip" style="width:{$mapping.width}px;left:{$mapping.x}px;top:{$mapping.y + $mapping.height - ($mapping.height / 2)}px">
						<span>{$mapping.title}</span>
					</div>
				{/if}
			{/foreach}
        </div>
    {/if}
</div>
