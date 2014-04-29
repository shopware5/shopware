{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
	{$sBreadcrumb = [['name'=>{$sSupport.name}, 'link'=>{url controller=ticket sFid=$sSupport.id}]]}
{/block}

{* Sidebar left
{block name='frontend_index_content_left'}
	{include file="frontend/index/sidebar.tpl"}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div class="content block forms--content panel">
		<div class="panel--body">
			<h1>{$sSupport.name}</h1>

			{if $sSupport.sElements}
				{eval var=$sSupport.text}
			{elseif $sSupport.text2}
				{include file="frontend/_includes/messages.tpl" type="success" content=$sSupport.text2}
			{/if}
		</div>

		<div class="forms--container panel has--border">
			<h2 class="panel--title is--underline">{$sSupport.name}</h2>

			<div class="panel--body">
				{if $sSupport.sElements}
					{block name='frontend_forms_index_elements'}
						{include file="frontend/forms/elements.tpl"}
					{/block}
				{elseif $sSupport.text2}
					<div class="space">&nbsp;</div>
					<a href="{url controller='index'}" class="btn btn--primary">{s name='FormsLinkBack'}{/s}</a>
				{else}
					<div class="col_center_container">
						<p>{s name='FormsTextContact'}{/s}</p>
						<a href="{url controller='index'}" class="btn btn--secondary">{s name='FormsLinkBack'}{/s}</a>
					</div>
				{/if}
			</div>
		</div>
		<div class="doublespace">&nbsp;</div>
	</div>
{/block}

{* Hide sidebar right *}
{block name='frontend_index_content_right'}{/block}
