{block name='frontend_register_error_messages'}
	{if $error_messages}
		<div class="alert error">
			<h3 class="register--alert-title">{s name='RegisterErrorHeadline'}{/s}</h3>

			{block name='frontend_register_error_messages_list'}
				<ul class="register--alert-list">

					{block name='fronrtend_register_error_messages_list_entry'}
						{foreach $error_messages as $errorItem}
							<li class="register--alert-entry">{$errorItem}</li>
						{/foreach}
					{/block}

				</ul>
			{/block}

		</div>
	{/if}
{/block}