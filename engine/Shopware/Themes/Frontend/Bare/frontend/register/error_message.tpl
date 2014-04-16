{block name='frontend_register_error_messages'}
	{if $error_messages}
		<div class="alert error">
			<h2 class="">{s name='RegisterErrorHeadline'}{/s}</h2>

			<ul>
				{foreach from=$error_messages item=errorItem}
					<li>{$errorItem}</li>
				{/foreach}
			</ul>
		</div>
	{/if}
{/block}