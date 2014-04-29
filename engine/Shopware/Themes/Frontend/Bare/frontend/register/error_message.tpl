{block name='frontend_register_error_messages'}
	{if $error_messages}
		{include file="frontend/_includes/messages.tpl" type="error" list=$error_messages}
	{/if}
{/block}