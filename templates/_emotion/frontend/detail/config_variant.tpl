{$configurator = $sArticle.sConfigurator}

{block name='frontend_detail_configurator_variant'}
	<div class="configurator--variant">

		{block name='frontend_detail_configurator_variant_form'}
			<form method="post" action="{url sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}" class="configurator--form">

				{foreach $configurator as $configuratorGroup}
					{block name='frontend_detail_configurator_variant_group'}
						<div class="variant--group">

							{block name='frontend_detail_configurator_variant_group_name'}
								<p class="variant--name">{$configuratorGroup.groupname}</p>
							{/block}

							{block name='frontend_detail_configurator_variant_group_options'}
								{foreach $configuratorGroup.values as $option}

									{block name='frontend_detail_configurator_variant_group_option'}
										<div class="variant--option">

											{block name='frontend_detail_configurator_variant_group_option_input'}
												<input type="radio"
													   class="option--input"
													   id="group[{$option.groupID}]"
													   name="group[{$option.groupID}]"
													   value="{$option.optionID}"
													   data-auto-submit="true"
													   {if !$option.selectable}disabled="disabled"{/if}
													   {if $option.selected && $option.selectable}checked="checked"{/if} />
											{/block}

											{block name='frontend_detail_configurator_variant_group_option_label'}
												<label for="group[{$option.groupID}]" class="option--label{if !$option.selectable} is--disabled{/if}">
													{if $option.media}
														{block name='frontend_detail_configurator_variant_group_option_label_image'}
															<img src="{if isset($option.media.src)}{$option.media.src.1}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$option.optionname}">
														{/block}
													{else}
														{block name='frontend_detail_configurator_variant_group_option_label_text'}
															{$option.optionname}
														{/block}
													{/if}
												</label>
											{/block}
										</div>
									{/block}
								{/foreach}
								<div class="clear">&nbsp;</div>
							{/block}
						</div>
					{/block}
				{/foreach}
			</form>
		{/block}
	</div>
{/block}