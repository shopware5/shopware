{extends file="frontend/account/select_billing.tpl"}

{* Include the necessary stylesheets. We need inline styles here due to the fact that the colors are configuratable. *}
{block name="frontend_index_header_css_screen" append}
    <style type="text/css">
        #confirm .table, #confirm .country-notice {
            background: {config name=baskettablecolor};
        }
        #confirm .table .table_head {
            color: {config name=basketheaderfontcolor};
            background: {config name=basketheadercolor};
        }
    </style>
{/block}

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

{* Hide footer *}
{block name="frontend_index_footer"}{/block}