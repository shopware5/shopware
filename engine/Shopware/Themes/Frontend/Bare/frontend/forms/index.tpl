{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
	{$sBreadcrumb = [['name'=>{$sSupport.name}, 'link'=>{url controller=ticket sFid=$sSupport.id}]]}
{/block}

{* Sidebar left *}
{block name='frontend_index_content_left'}
	{include file="frontend/index/sidebar.tpl"}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div class="content block forms--content panel right">
		<div class="panel--body is--wide">
			{if $sSupport.sElements}
				<h1>{$sSupport.name}</h1>
				{eval var=$sSupport.text}
			{elseif $sSupport.text2}
				{include file="frontend/_includes/messages.tpl" type="success" content=$sSupport.text2}
			{/if}
		</div>

		{if $sSupport.sElements}
		<div class="forms--container panel has--border">
			<h2 class="panel--title is--underline">{$sSupport.name}</h2>
			<div class="panel--body">
				{block name='frontend_forms_index_elements'}
					{include file="frontend/forms/elements.tpl"}
				{/block}
			</div>
		</div>
		{/if}

	</div>
{/block}

{* Hide sidebar right *}
{block name='frontend_index_content_right'}{/block}
