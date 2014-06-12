{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
	{$sBreadcrumb = [['name'=>"{s name='NoteTitle'}{/s}", 'link'=>{url}]]}
{/block}

{* Account Sidebar *}
{block name="frontend_index_left_categories" prepend}
	{block name="frontend_account_sidebar"}
		{include file="frontend/account/sidebar.tpl"}
	{/block}
{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div class="content block note--content">

		{* Infotext *}
		{block name="frontend_note_index_welcome"}
			<div class="account--welcome panel">
				{block name="frontend_note_index_welcome_headline"}
					<h1 class="panel--title">{s name="NoteHeadline"}{/s}</h1>
				{/block}

				{block name="frontend_note_index_welcome_content"}
					<div class="panel--body is--wide">
						<p>{s name="NoteText"}{/s}</p>
						<p>{s name="NoteText2"}{/s}</p>
					</div>
				{/block}
			</div>
		{/block}

		{block name="frontend_note_index_overview"}
			<div class="note--overview">
				{if $sNotes}
					{block name="frontend_note_index_table"}
						<div class="note--table panel--table has--border">

							{* Table head *}
							{block name="frontend_note_index_table_head"}
								<div class="note--table-head panel--tr">

									{* Article informations *}
									{block name="frontend_note_index_table_head_name"}
										<div class="panel--th note--info">{s name="NoteColumnName"}{/s}</div>
									{/block}

									{* Unit price *}
									{block name="frontend_note_index_table_head_price"}
										<div class="panel--th note--sale">{s name="NoteColumnPrice"}{/s}</div>
									{/block}

									{block name="frontend_note_index_table_columns"}{/block}
								</div>
							{/block}

							{block name="frontend_note_index_table_items"}
								{foreach from=$sNotes item=sBasketItem name=noteitems}
									{if $smarty.foreach.noteitems.last}
										{assign var=lastrow value=1}
									{else}
										{assign var=lastrow value=0}
									{/if}

									{include file="frontend/note/item.tpl" lastrow=$lastrow}
								{/foreach}
							{/block}
						</div>
					{/block}
				{/if}
			</div>
		{/block}

	</div>
{/block}