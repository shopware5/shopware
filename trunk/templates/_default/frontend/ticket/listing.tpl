{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
	{$sBreadcrumb = [['name'=>"{s name='TicketTitle'}{/s}", 'link'=>{url}]]}
{/block}

{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="ticket" class="grid_16">
	
	{* Ticketsystem headline *}
	<h1>{se name='TicketHeadline'}{/se}</h1>
	
	<div class="ticketoverview">
		{block name='frontend_ticket_table_head'}
		<div class="header grid_16 first last">
			<div class="grid_3">{s name='TicketInfoDate'}{/s}</div>
			<div class="grid_3">{s name='TicketInfoId'}{/s}</div>
			<div class="grid_3">{s name='TicketInfoStatus'}{/s}</div>
			<div class="grid_3">&nbsp;</div>
		</div>
		{/block}
		<div class="clear">&nbsp;</div>
		
		<div class="content">
			{foreach from=$ticketStore.data item=ticketItem}
				{cycle assign=column_color values='#F9F9F9,#FFFFFF'}
				{block name='frontend_ticket_entry'}
				<div class="row" style="background-color:{$column_color}">	
					<div class="grid_3">{$ticketItem.receipt}</div>
					<div class="grid_3">#{$ticketItem.id}</div>
					<div class="grid_3" style="{if $ticketItem.status_color != '0'}color:{$ticketItem.status_color}{/if}">{$ticketItem.status}</div>
					
					<div class="grid_3" style="background-color:{$column_color};">
						<a href="{url controller='ticket' action='detail' tid=$ticketItem.id}" class="button-middle small">
							{se name='TicketLinkDetails'}{/se}
						</a>
					</div>
				</div>
				{/block}
			{/foreach}
		</div>
	</div>
	<div class="space">&nbsp;</div>	
</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
<div id="right_account" class="grid_4 last">
	{include file='frontend/ticket/navigation.tpl'}
</div>
{/block}