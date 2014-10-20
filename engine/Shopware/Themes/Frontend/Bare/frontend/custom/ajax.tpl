<div class="buttons--off-canvas">
    <a href="#" title="{s name="CustomAjaxActionClose"}Close{/s}" class="close--off-canvas">
        <i class="icon--arrow-left"></i>
        {s name="CustomAjaxActionClose"}Close{/s}
    </a>
</div>
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