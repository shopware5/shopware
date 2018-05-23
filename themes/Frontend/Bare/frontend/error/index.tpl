{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header_title'}{s name="ErrorIndexTitle"}{/s} | {$smarty.block.parent}{/block}

{block name='frontend_index_content'}
    {if $isCsrfException}
        {include file='frontend/error/csrf.tpl'}
    {else}
        {include file='frontend/error/exception.tpl'}
    {/if}
{/block}

{* Disable header items *}
{block name='frontend_index_actions'}{/block}
{block name='frontend_index_checkout_actions'}{/block}
{block name='frontend_index_search'}{/block}

{* Disable left navigation *}
{block name='frontend_index_content_left'}{/block}

{* Disable top bar *}
{block name='frontend_index_top_bar_container'}{/block}

{* Disable search bar / my account button / basket button *}
{block name='frontend_index_shop_navigation'}{/block}
