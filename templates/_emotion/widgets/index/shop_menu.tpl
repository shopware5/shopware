<div id="topbar">

{* Currency changer *}
{block name='frontend_index_actions_currency'}
    {if $currencies|count > 1}
        <div class="topbar_currency">
            {foreach from=$currencies item=currency}
                <form action="" method="post" class="currency">
                    <input type="hidden" name="__currency" value="{$currency->getId()}" />
                    <input type="submit" {if $currency->getId() === $shop->getCurrency()->getId()}class="active"{/if} value="{$currency->getCurrency()}" />
                </form>
            {/foreach}
        </div>
    {/if}
{/block}

{* Active language *}
{block name='frontend_index_actions_active_shop'}
{if $shop && $languages|count > 1}
<div class="topbar_lang">
    {if $shop && $languages|count > 1}
        <div class="flag {$shop->getLocale()->toString()}">{$shop->getName()}</div>
    {/if}
    {if $languages|count > 1}
        <form method="post" action="">
            <select name="__shop" class="lang_select auto_submit">
                {foreach from=$languages item=language}
                    <option value="{$language->getId()}" {if $language->getId() === $shop->getId()}selected="selected"{/if}>
                        {$language->getName()}
                    </option>
                {/foreach}
            </select>
            <input type="hidden" name="__redirect" value="1">
        </form>
    {/if}
</div>
{/if}
{/block}

</div>