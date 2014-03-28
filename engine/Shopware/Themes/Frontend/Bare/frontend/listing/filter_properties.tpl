{* Filter properites *}
{block name="frontend_listing_filter_properties"}
	{if $sPropertiesOptionsOnly|@count}
		{block name="frontend_listing_filter_properties_box"}
			{if $sPropertiesGrouped|@count > 1 && $sCategoryContent.showFilterGroups}
				{foreach from=$sPropertiesGrouped item=sPropertyGroup key=name}
					<a class="filter--link" href="{$sPropertyGroup.default.linkSelect}" title="{$sCategoryInfo.name}">
						<span{if $activeFilterGroup == $name} class="filter--indicator is--active"{/if}>{$name}</span>
					</a>
					{if $activeFilterGroup == $name}
						{foreach from=$sPropertiesOptionsOnly item=value key=option}
							{if $value.properties.group === $name}
								<h5 class="filter--headline">{$option}</h5>
								<ul class="filter--list is-active">
									{foreach from=$value.values item=optionValue}
										{if $optionValue.active}
											<li class="filter--entry">
                                                {if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if}
												({$optionValue.count})
											</li>
										{else}
											<li class="filter--entry">
                                                <a class="filter--link" href="{$optionValue.link}" title="{$sCategoryInfo.name}">
                                                    {if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if} {if $optionValue.count > 0}({$optionValue.count}){/if}
                                                </a>
											</li>
										{/if}
									{/foreach}
									{if $value.properties.active}
										<li class="filter--entry">
                                            <a class="filter--link link--close" href="{$value.properties.linkRemoveProperty}" title="{$sCategoryInfo.name}">
                                                {s name='FilterLinkDefault'}{/s}
                                            </a>
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
						<span{if $value.properties.active} class="filter--indicator is--active"{/if}>
                            {$option} <span class="filter--expand-collapse">+</span>
                        </span>
						<div class="filter--content">
							<ul class="filter--list">
								{foreach from=$value.values item=optionValue}
									{if $optionValue.active}
										<li class="filter--entry is--active">
											{if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if}
                                            {if $optionValue.count > 0}({$optionValue.count}){/if}
										</li>
									{else}
										<li class="filter--entry">
											<a class="filter--link" href="{$optionValue.link}" title="{$sCategoryInfo.name}">
												{if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if} {if $optionValue.count > 0}({$optionValue.count}){/if}
											</a>
										</li>
									{/if}
								{/foreach}
								{if $value.properties.active}
									<li class="filter--entry">
										<a class="filter--link link--close" href="{$value.properties.linkRemoveProperty}" title="{$sCategoryInfo.name}">
											{s name='FilterLinkDefault'}{/s}
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