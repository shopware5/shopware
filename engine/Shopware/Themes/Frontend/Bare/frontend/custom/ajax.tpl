<div class="ajax-modal--custom">
	{block name='frontend_custom_ajax_action_buttons'}
		<h2 class="custom--title">{$sCustomPage.description}</h2>
	{/block}
	{* Article content *}
	{block name='frontend_custom_ajax_article_content'}
		<div class="custom--content">
			{$sContent}
		</div>
	{/block}
</div>