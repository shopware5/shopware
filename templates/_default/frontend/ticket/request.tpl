{extends file='frontend/forms/index.tpl'}

{block name='frontend_forms_index_elements'}
	{include file="frontend/ticket/elements.tpl"}
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
<div id="right_account" class="grid_4 last">
	{include file="frontend/ticket/navigation.tpl"}
</div>
{/block}
