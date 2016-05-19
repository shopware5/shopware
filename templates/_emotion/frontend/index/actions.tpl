<div id="topbar">
	{* Currency changer *}
	{block name='frontend_index_actions_currency'}
	{if $sCurrencies && $sCurrencies|@count > 1}
		{foreach from=$sCurrencies item=sCurrency}
		<form method="post" class="currency">
			<input type="hidden" name="sCurrency" value="{$sCurrency.id}" />
			<input type="submit" {if $sCurrency.flag}class="active"{/if} value="{$sCurrency.currency}" />
		</form>
		{/foreach}
	{/if}
	{/block}

	{* Active language *}
	{block name='frontend_index_actions_active_shop'}
        {if $Shop && $sLanguages && $sLanguages|count > 1}
            <div class="flag {$Shop->getLocale()->toString()}">{$Shop->getTitle()|default:$Shop->getName()}</div>
        {/if}
	{/block}

	{* Language changer *}
	{block name='frontend_index_actions_shop'}
	{if $sLanguages && $sLanguages|@count > 1}
	<form method="post" action="{url controller='index'}">
		<select name="sLanguage" class="lang_select auto_submit">
			{foreach from=$sLanguages item=sLanguage}
				<option value="{$sLanguage.id}" {if $sLanguage.flag}selected="selected"{/if}>
				 {$sLanguage.name}
				</option>
			{/foreach}
		</select>
	</form>
	{/if}
	{/block}
</div>
