{block name='frontend_detail_configurator_error'}
	{if $sArticle.sError && $sArticle.sError.variantNotAvailable}
		<div class="error">{s name='VariantAreNotAvailable'}Die ausgewählte Variante steht aktuell nicht zur Verfügung{/s}</div>
	{/if}
{/block}

<form method="post" action="{url sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}" class="confgurator--form selection--form">
	{foreach from=$sArticle.sConfigurator item=sConfigurator name=group key=groupID}
	
		{* Group name *}
		{block name='frontend_detail_group_name'}
			<strong class="content--title">{$sConfigurator.groupname}</strong>
		{/block}
		
		{* Group description *}
		{block name='frontend_detail_group_description'}
			{if $sConfigurator.groupdescription}
				<p class="content--description">{$sConfigurator.groupdescription}</p>
			{/if}
		{/block}
		
		{$pregroupID=$groupID-1}
		{* Configurator drop down *}
		{block name='frontend_detail_group_selection'}
			<div class="field--select{if $groupID gt 0 && empty($sArticle.sConfigurator[$pregroupID].user_selected)} is--disabled{/if}">
				<span class="arrow"></span>
				<select{if $groupID gt 0 && empty($sArticle.sConfigurator[$pregroupID].user_selected)} disabled="disabled"{/if} name="group[{$sConfigurator.groupID}]">

					{* Please select... *}
					{if empty($sConfigurator.user_selected)}
						<option value="" selected="selected">{s name="DetailConfigValueSelect"}{/s}</option>
					{/if}

					{foreach from=$sConfigurator.values item=configValue name=option key=optionID}
						{if !isset($configValue.active)||$configValue.active==1}
							<option{if $configValue.selected&&$sConfigurator.user_selected} selected="selected"{/if} value="{$configValue.optionID}">
								{$configValue.optionname}{if $configValue.upprice && !$configValue.reset} {if $configValue.upprice > 0}{/if}{/if}
							</option>
						{/if}
					{/foreach}
				</select>
			</div>
		{/block}
	{/foreach}

	{block name='frontend_detail_configurator_noscript_action'}
		<noscript>
			<input name="recalc" type="submit" value="{s name='DetailConfigActionSubmit'}{/s}" />
		</noscript>
	{/block}
</form>