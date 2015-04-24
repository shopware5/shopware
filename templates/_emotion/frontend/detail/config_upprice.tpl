
<form method="post" action="{url sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}" class="upprice_config">

	{foreach from=$sArticle.sConfigurator item=sConfigurator}
		
		{* Group name *}
		{block name='frontend_detail_group_name'}
		<p>
			<strong>{$sConfigurator.groupname}</strong>
		</p>
		{/block}
		
		{* Group description *}
		{if $sConfigurator.groupdescription}
			{block name='frontend_detail_group_description'}
				<p class="groupdescription">{$sConfigurator.groupdescription}</p>
			{/block}
		{/if}
		
		<select name="group[{$sConfigurator.groupID}]" onChange="this.form.submit();">
			{foreach from=$sConfigurator.values item=configValue}
				{if !{config name=hideNoInStock} || ({config name=hideNoInStock} && $configValue.selectable)}
					<option {if $configValue.selected}selected="selected"{/if} value="{$configValue.optionID}">
						{$configValue.optionname}{if $configValue.upprice} {if $configValue.upprice > 0}{/if}{/if}
					</option>
				{/if}
			{/foreach}
		</select>
	{/foreach}
	
	<noscript>
		<input name="recalc" type="submit" value="{s name='DetailConfigActionSubmit'}{/s}" />
	</noscript>
</form>
