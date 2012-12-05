{block name='frontend_register_error_messages'}
{if $error_messages}
	<div class="error"><strong>{s name='RegisterErrorHeadline'}{/s}</strong><br />
		{foreach from=$error_messages item=errorItem}{$errorItem}<br />{/foreach}
	</div>
{/if}
{/block}