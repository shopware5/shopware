{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
	{$sBreadcrumb = [['name'=>"{s name='NoteTitle'}{/s}", 'link'=>{url}]]}
{/block}

{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name="frontend_index_content"}
	<div id="notes" class="grid_16">

		{* Infobox *}
		<div class="cat_text grid_16{if !$sUserLoggedIn} full_length{/if}">
			<div class="inner_container">
				<h1>{s name='NoteHeadline'}{/s}</h1>
				<p>
					{s name='NoteText'}{/s}
				</p>
				<p>
					{s name='NoteText2'}{/s}
				</p>
			</div>
		</div>

		<div class="space">&nbsp;</div>

		<div class="note{if !$sUserLoggedIn} full_length{/if}">

			{if $sNotes}
				<div class="table note{if !$sUserLoggedIn} full_length{/if}">
				{* Table head *}
				<div class="table_head">

					{* Article informations *}
					<div class="grid_12">
						{s name='NoteColumnName'}{/s}
					</div>

					{* Unit price *}
					<div class="grid_3">
						{s name='NoteColumnPrice'}{/s}
					</div>
					{block name="frontend_note_index_columns"}{/block}
				</div>

				{foreach from=$sNotes item=sBasketItem name=noteitems}
					{if $smarty.foreach.noteitems.last}
						{assign var=lastrow value=1}
					{else}
						{assign var=lastrow value=0}
					{/if}

					{include file="frontend/note/item.tpl" lastrow=$lastrow}

				{/foreach}
			</div>
			{/if}
		</div>
		<div class="doublespace">&nbsp;</div>
	</div>
{/block}

{* Sidebar right *}
{block name="frontend_index_content_right"}
{if $sUserLoggedIn}
	<div id="right_account" class="grid_4 last">
		{include file="frontend/account/content_right.tpl"}
	</div>
{/if}
{/block}