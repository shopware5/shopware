{extends file='frontend/index/index.tpl'}

{* Empty sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main contents *}
{block name='frontend_index_content'}
<div class="grid_16">

{if !$ticketDetails.id}
	<div class="error">
	{se name='TicketDetailInfoEmpty'}{/se}
	</div>
{else}
	
	{* Ticket headline *}
	{block name='frontend_ticket_headline'}
	<h2>{se name='TicketDetailInfoTicket'}{/se} #{$ticketDetails.id}</h2>
	{/block}
	
	{* Error messages *}
	{if $error!=""}
		<div class="error">{$error}</div>
	{/if}
	
	{if $accept!=""}
		{* Ticket status *}
		<div>{$accept}</div>
	{/if}
	
	{* Ticket closed *}
	{if $ticketDetails.closed}
	 	<div class="success">
		{se name='TicketDetailInfoStatusClose'}{/se}
		</div>
	{* Ticket in process *}
	{elseif !$ticketDetails.responsible}
		<div class="notice bold center">{se name='TicketDetailInfoStatusProgress'}{/se}</div>
	{* Ticket answer *}
	{else}
		{block name='frontend_ticket_answer'}
		<div class="tickeranswer">
			<form action="" method="POST">
				<h2>{se name='TicketDetailInfoAnswer'}{/se}:</h2>
				<textarea name="sAnswer"></textarea>
				
				<input class="button-right large" type="submit" value="Senden" name="sSubmit"/>
			</form>
		</div>
		{/block}
	{/if}

    {foreach from=$ticketHistoryDetails item=historyItem}
        <label class="ticketdetail_lbl">
            {$historyItem.receipt|date_format:"%d.%m.%Y %H:%M:%S"} |
            {if $historyItem.direction == "OUT"}
                {se name='TicketDetailInfoShopAnswer'}{/se}
                {else}
                {se name='TicketDetailInfoAnswer'}{/se}
            {/if}:</label>

    {* Your message *}
        {block name='frontend_ticket_history_your_message'}
            <div class="ticketdetail_txtbox">{$historyItem.message}</div>
        {/block}
    {/foreach}

	{* Ticket meta data *}
	{block name='frontend_ticket_meta_data'}
		<label class="ticketdetail_lbl">{$ticketDetails.receipt|date_format:"%d.%m.%Y %H:%M:%S"} | {se name='TicketDetailInfoQuestion'}{/se}</label>
		<div class="ticketdetail_txtbox">{$ticketDetails.message}</div>
	{/block}

{/if}
	<a href="{url controller='ticket' action='listing'}" class="button-left large">{s name='TicketDetailLinkBack'}{/s}</a>
	<div class="space">&nbsp;</div>
</div>
{/block}
{block name='frontend_index_content_right'}
<div id="right_account" class="grid_4 last">
	{include file='frontend/ticket/navigation.tpl'}
</div>
{/block}