{* Filter properites *}
{block name="frontend_listing_filter_properties"}
	{if $sPropertiesOptionsOnly|@count}
		{block name="frontend_listing_filter_properties_box"}
			{if $sPropertiesGrouped|@count > 1 && $sCategoryContent.showFilterGroups}
				{foreach from=$sPropertiesGrouped item=sPropertyGroup key=name}
					<a href="{$sPropertyGroup.default.linkSelect}" title="{$sCategoryInfo.name}">
						<div{if $activeFilterGroup == $name} class="active"{/if}>{$name|escape}</div>
					</a>
					{if $activeFilterGroup == $name}
						{foreach from=$sPropertiesOptionsOnly item=value key=option}
							{if $value.properties.group === $name}
								<h5 class="bold">{$option}</h5>
								<ul class="active">
									{foreach from=$value.values item=optionValue}
										{if $optionValue.active}
											<li>{if $optionValue.valueTranslation}{$optionValue.valueTranslation|escape}{else}{$optionValue.value|escape}{/if}
												({$optionValue.count})
											</li>
										{else}
											<li><a href="{$optionValue.link}"
												   title="{$sCategoryInfo.name}">{if $optionValue.valueTranslation}{$optionValue.valueTranslation|escape}{else}{$optionValue.value|escape}{/if} {if $optionValue.count > 0}({$optionValue.count}){/if}</a>
											</li>
										{/if}
									{/foreach}
									{if $value.properties.active}
										<li class="close"><a href="{$value.properties.linkRemoveProperty}"
															 title="{$sCategoryInfo.name}">{s name='FilterLinkDefault'}{/s}</a>
										</li>
									{/if}
								</ul>
							{/if}
						{/foreach}
					{/if}
				{/foreach}
			{else}
				{foreach from=$sPropertiesOptionsOnly item=value key=option}
					{if $value|@count}
						<div{if $value.properties.active} class="active"{/if}>{$option|escape} <span
									class="expandcollapse">+</span></div>
						<div class="slideContainer">
							<ul>
								{foreach from=$value.values item=optionValue}
									{if $optionValue.active}
										<li class="active">
											{if $optionValue.valueTranslation}{$optionValue.valueTranslation|escape}{else}{$optionValue.value|escape}{/if} {if $optionValue.count > 0}({$optionValue.count}){/if}
										</li>
									{else}
										<li>
											<a href="{$optionValue.link}" title="{$sCategoryInfo.name}">
												{if $optionValue.valueTranslation}{$optionValue.valueTranslation|escape}{else}{$optionValue.value|escape}{/if} {if $optionValue.count > 0}({$optionValue.count}){/if}
											</a>
										</li>
									{/if}
								{/foreach}
								{if $value.properties.active}
									<li class="close">
										<a href="{$value.properties.linkRemoveProperty}" title="{$sCategoryInfo.name}">
											{se name='FilterLinkDefault'}{/se}
										</a>
									</li>
								{/if}
							</ul>
						</div>
					{/if}
				{/foreach}
			{/if}
		{/block}
	{/if}
{/block}