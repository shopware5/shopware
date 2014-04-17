{block name='frontend_register_error_messages'}
	{if $error_messages}
		<div class="register--alert">
			<div class="alert error">
				<h3 class="">{s name='RegisterErrorHeadline'}{/s}</h3>
				<ul>
					{foreach from=$error_messages item=errorItem}
						<li>{$errorItem}</li>
					{/foreach}
				</ul>
			</div>
		</div>
	{/if}
{/block}