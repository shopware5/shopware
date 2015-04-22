<form method="post" action="{url sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}" class="configurator--form upprice--form">

	{foreach $sArticle.sConfigurator as $sConfigurator}
		
		{* Group name *}
		{block name='frontend_detail_group_name'}
			<p class="configurator--label">{$sConfigurator.groupname}:</p>
		{/block}
		
		{* Group description *}
		{if $sConfigurator.groupdescription}
			{block name='frontend_detail_group_description'}
				<p class="configurator--description">{$sConfigurator.groupdescription}</p>
			{/block}
		{/if}

		{* Configurator drop down *}
		{block name='frontend_detail_group_selection'}
			<select name="group[{$sConfigurator.groupID}]" data-auto-submit="true">
				{foreach $sConfigurator.values as $configValue}
					<option {if !$configValue.selectable}disabled{/if} {if $configValue.selected && $sConfigurator.user_selected} selected="selected"{/if} value="{$configValue.optionID}">
						{$configValue.optionname}{if $configValue.upprice && !$configValue.reset} {if $configValue.upprice > 0}{/if}{/if}
						{if !$configValue.selectable}{s name="DetailConfigValueNotAvailable" namespace="frontend/detail/config_step"}{/s}{/if}
					</option>
				{/foreach}
			</select>
		{/block}
	{/foreach}

	{block name='frontend_detail_configurator_noscript_action'}
		<noscript>
			<input name="recalc" type="submit" value="{s name='DetailConfigActionSubmit'}{/s}" />
		</noscript>
	{/block}
</form>