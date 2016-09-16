{* Language switcher *}
{block name='frontend_index_actions_active_shop'}
    {if $shop && $languages|count > 1}
        <div class="top-bar--language navigation--entry">
            {block name='frontend_index_actions_active_shop_top_bar_language'}
                {if $languages|count > 1}
                    <form method="post" class="language--form">
                        {block name="frontend_index_actions_active_shop_language_form_content"}
                            <div class="field--select">
                                {if $shop && $languages|count > 1}
                                    <div class="language--flag {$shop->getLocale()->toString()}">{$shop->getName()}</div>
                                {/if}
                                {block name="frontend_index_actions_active_shop_language_form_select"}
                                    <select name="__shop" class="language--select" data-auto-submit="true">
                                        {foreach $languages as $language}
                                            <option value="{$language->getId()}" {if $language->getId() === $shop->getId()}selected="selected"{/if}>
                                                {{$language->getName()}|snippet:"frontend_shopname_{$language->getId()}":"frontend"}
                                            </option>
                                        {/foreach}
                                    </select>
                                {/block}
                                <input type="hidden" name="__redirect" value="1">
                                {block name="frontend_index_actions_active_shop_inline"}{/block}
                                <span class="arrow"></span>
                            </div>
                        {/block}
                    </form>
                {/if}
            {/block}
        </div>
    {/if}
{/block}

{* Currency changer *}
{block name='frontend_index_actions_currency'}
    {if $currencies|count > 1}
        <div class="top-bar--currency navigation--entry">
            {block name='frontend_index_actions_currency_form'}
                <form method="post" class="currency--form">
                    {block name="frontend_index_actions_currency_form_content"}
                        <div class="field--select"> {block name="frontend_index_actions_currency_form_select"}
                            <select name="__currency" class="currency--select" data-auto-submit="true">
                                {foreach $currencies as $currency}
                                    <option value="{$currency->getId()}"{if $currency->getId() === $shop->getCurrency()->getId()} selected="selected"{/if}>
                                        {$currency->getSymbol()} {$currency->getCurrency()}
                                    </option>
                                {/foreach}
                            </select>
                            {/block}
                            <span class="arrow"></span>
                        </div>
                    {/block}
                </form>
            {/block}
        </div>
    {/if}
{/block}
