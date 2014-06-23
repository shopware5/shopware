{if $sArticle.sError && $sArticle.sError.variantNotAvailable}
    <div class="error">{s name='VariantAreNotAvailable'}Die ausgewählte Variante steht aktuell nicht zur Verfügung{/s}</div>
{/if}
<form method="post" action="{url sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}" class="config_select">
    <input type="hidden" name="new" value="{$new}" />
	{foreach from=$sArticle.sConfigurator item=sConfigurator name=group key=groupID}
	
		{* Group name *}
		{block name='frontend_detail_group_name'}
		<p>
			<strong>{$sConfigurator.groupname}</strong>
		</p>
		{/block}
		
		{* Group description *}
		{block name='frontend_detail_group_description'}
		{if $sConfigurator.groupdescription}
		<p class="groupdescription">
			{$sConfigurator.groupdescription}
		</p>
		{/if}
		{/block}

        <select name="group[{$sConfigurator.groupID}]" onChange="this.form.submit();">

            {* Please select... *}
            {if !$sConfigurator.user_selected}
                <option value="" selected="selected">{s name="DetailConfigValueSelect"}{/s}</option>
            {else}
                <option value="">{s name="DetailConfigValueReset"}{/s}</option>
            {/if}

            {foreach from=$sConfigurator.values item=configValue name=option key=optionID}
                {if $configValue.selected}
                    <option value="{$configValue.optionID}" selected="selected">
                        {$configValue.optionname}
                        {if $configValue.upprice && !$configValue.reset}
                            {if $configValue.upprice > 0}{/if}
                        {/if}
                    </option>
                {else}
                    <option value="{$configValue.optionID}">
                        {$configValue.optionname}
                        {if $configValue.upprice && !$configValue.reset}
                            {if $configValue.upprice > 0}{/if}
                        {/if}
                    </option>
                {/if}
            {/foreach}
        </select>
	{/foreach}
	
	<noscript>
		<input name="recalc" type="submit" value="{s name='DetailConfigActionSubmit'}{/s}" />
	</noscript>
</form>