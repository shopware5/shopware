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
										<div class="variant--option{if $option.media} is--image{/if}">

											{block name='frontend_detail_configurator_variant_group_option_input'}
												<input type="radio"
													   class="option--input"
													   id="group[{$option.groupID}]"
													   name="group[{$option.groupID}]"
													   value="{$option.optionID}"
													   data-auto-submit="true"
													   {if !$option.selectable}disabled="disabled"{/if}
													   {if $option.selected}checked="checked"{/if} />
											{/block}

											{block name='frontend_detail_configurator_variant_group_option_label'}
												<label for="group[{$option.groupID}]" class="option--label{if !$option.selectable} is--disabled{/if}">

													{if $option.media}
														{$media = $option.media}

														{block name='frontend_detail_configurator_variant_group_option_label_image'}
															<span data-picture data-alt="{$configuratorOption.optionname}" class="image--element">
																<span class="image--media" data-src="{if isset($media.src)}{$media.src.1}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
																<span class="image--media" data-src="{if isset($media.src)}{$media.src.2}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 48em)"></span>
																<span class="image--media" data-src="{if isset($media.src)}{$media.src.3}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 78.75em)"></span>

																<noscript>
																	<img src="{if isset($media.src)}{$media.src.1}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$option.optionname}">
																</noscript>
															</span>
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
							{/block}
						</div>
					{/block}
				{/foreach}
			</form>
		{/block}
	</div>
{/block}