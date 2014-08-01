{if $sArticle.sError && $sArticle.sError.variantNotAvailable}
    <div class="error">{s name='VariantAreNotAvailable'}Die ausgewählte Variante steht aktuell nicht zur Verfügung{/s}</div>
{/if}
<form method="post" action="{url sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}" class="config_select">
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

        {assign var="pregroupID" value=$groupID-1}
        <select {if $groupID gt 0&&empty($sArticle.sConfigurator[$pregroupID].user_selected)}disabled="disabled"{/if}name="group[{$sConfigurator.groupID}]" onChange="this.form.submit();">

            {* Please select... *}
            {if empty($sConfigurator.user_selected)}
                <option value="" selected="selected">{s name="DetailConfigValueSelect"}{/s}</option>
            {/if}

            {foreach from=$sConfigurator.values item=configValue name=option key=optionID}
                <option {if !$configValue.selectable}disabled{/if} {if $configValue.selected && $sConfigurator.user_selected} selected="selected"{/if} value="{$configValue.optionID}">
                    {$configValue.optionname}{if $configValue.upprice && !$configValue.reset} {if $configValue.upprice > 0}{/if}{/if}
                    {if !$configValue.selectable}{s name="DetailConfigValueNotAvailable"}{/s}{/if}
                </option>
            {/foreach}
        </select>
    {/foreach}

    <noscript>
        <input name="recalc" type="submit" value="{s name='DetailConfigActionSubmit'}{/s}" />
    </noscript>
</form>
