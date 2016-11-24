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
            <select name="group[{$sConfigurator.groupID}]"{if $theme.ajaxVariantSwitch} data-ajax-select-variants="true"{else} data-auto-submit="true"{/if}>
                {foreach $sConfigurator.values as $configValue}
                    {if !{config name=hideNoInStock} || ({config name=hideNoInStock} && $configValue.selectable)}
                        <option{if $configValue.selected} selected="selected"{/if} value="{$configValue.optionID}">
                            {$configValue.optionname}{if $configValue.upprice} {if $configValue.upprice > 0}{/if}{/if}
                        </option>
                    {/if}
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
