{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
	{$sBreadcrumb = [['name'=>{$sSupport.name}, 'link'=>{url controller=ticket sFid=$sSupport.id}]]}
{/block}

{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_16 supportrequest">
	<h2 class="headingbox_dark largesize">{$sSupport.name}</h2>
	<div class="inner_container">
	{if $sSupport.sElements}
		<div class="col_center_custom">
			{eval var=$sSupport.text}
		</div>
		<div class="space">&nbsp;</div>
		{block name='frontend_forms_index_elements'}
			{include file="frontend/forms/elements.tpl"}
		{/block}
	{elseif $sSupport.text2}
			<div class="success center bold">
				{eval var=$sSupport.text2}
			</div>
			<div class="space">&nbsp;</div>
			<a href="{url controller='index'}" class="button-left large">{s name='FormsLinkBack'}{/s}</a>
	{else}
		<div class="col_center_container">
			<p>{s name='FormsTextContact'}{/s}</p>
			<a href="{url controller='index'}" class="button-left large">{s name='FormsLinkBack'}{/s}</a>
		</div>
	{/if}
	</div>
	<div class="doublespace">&nbsp;</div>
</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
	{include file="frontend/custom/right.tpl"}
{/block}