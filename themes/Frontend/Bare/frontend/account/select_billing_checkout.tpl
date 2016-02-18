{extends file="frontend/account/select_billing.tpl"}

{* Shop header *}
{block name='frontend_index_navigation'}
    {include file="frontend/checkout/header.tpl"}
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}{/block}

{* Step box *}
{block name='frontend_index_navigation_categories_top'}
    {include file="frontend/register/steps.tpl" sStepActive="address"}
{/block}

{* Footer *}
{block name="frontend_index_footer"}
    {if !$theme.checkoutFooter}
        {$smarty.block.parent}
    {else}
        {block name="frontend_index_account_select_billing_checkout_footer"}
            {include file="frontend/index/footer_minimal.tpl"}
        {/block}
    {/if}
{/block}