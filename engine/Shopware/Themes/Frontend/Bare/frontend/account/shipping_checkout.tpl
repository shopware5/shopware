{extends file='frontend/account/shipping.tpl'}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}{/block}

{block name="frontend_index_content_top"}
    {* Step box *}
    {include file="frontend/register/steps.tpl" sStepActive="address"}
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}