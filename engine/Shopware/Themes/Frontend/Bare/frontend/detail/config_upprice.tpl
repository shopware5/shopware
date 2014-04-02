<form method="post" action="{url sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}" class="confgurator--form upprice--form">

	{foreach $sArticle.sConfigurator as $sConfigurator}
		
		{* Group name *}
		{block name='frontend_detail_group_name'}
			<strong class="content--title">{$sConfigurator.groupname}</strong>
		{/block}
		
		{* Group description *}
		{if $sConfigurator.groupdescription}
			{block name='frontend_detail_group_description'}
				<p class="content--description">{$sConfigurator.groupdescription}</p>
			{/block}
		{/if}

		{* Configurator drop down *}
		{block name='frontend_detail_group_selection'}
			<div class="field--select">
				<span class="arrow"></span>
				<select name="group[{$sConfigurator.groupID}]">
					{foreach $sConfigurator.values as $configValue}
						<option{if $configValue.selected} selected="selected"{/if} value="{$configValue.optionID}">
							{$configValue.optionname}{if $configValue.upprice} {if $configValue.upprice > 0}{/if}{/if}
						</option>
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