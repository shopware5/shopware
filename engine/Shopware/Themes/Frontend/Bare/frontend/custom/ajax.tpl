<div class="ajax-modal--custom">
	{block name='frontend_custom_ajax_action_buttons'}
		<div class="panel--title is--underline">{$sCustomPage.description}</div>
	{/block}
	{* Article content *}
	{block name='frontend_custom_ajax_article_content'}
		<div class="panel--body is--wide">
			{$sContent}
		</div>
	{/block}
</div>