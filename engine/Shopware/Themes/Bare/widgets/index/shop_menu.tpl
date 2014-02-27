{* Language switcher *}
{block name='frontend_index_actions_active_shop'}
    {if $shop && $languages|count > 1}
        <div class="top-bar--language">
            {if $languages|count > 1}
                <form method="post" action="{$smarty.server.REQUEST_URI}" class="language--form">
                    <select name="__shop" class="language--select">
                        {foreach $languages as $language}
                            <option value="{$language->getId()}" {if $language->getId() === $shop->getId()}selected="selected"{/if}>
                                {$language->getName()}
                            </option>
                        {/foreach}
                    </select>
                </form>
            {/if}
        </div>
    {/if}
{/block}

{* Currency changer *}
{block name='frontend_index_actions_currency'}
    {if $currencies|count > 1}
        <div class="top-bar--currency">
            {foreach $currencies as $currency}
                <form action="{$smarty.server.REQUEST_URI}" method="post" class="currency--form">
                    <input type="hidden" name="__currency" value="{$currency->getId()}" />
                    <input type="submit" {if $currency->getId() === $shop->getCurrency()->getId()}class="is--active"{/if} value="{$currency->getCurrency()}" />
                </form>
            {/foreach}
        </div>
    {/if}
{/block}