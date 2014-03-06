{* Search container *}
{block name='frontend_index_search_container'}
    <form action="{url controller='search' fullPath=false}" method="get" class="main-search--form">
        {block name="frontend_index_search_innerform"}{/block}
		<i class="icon--search"></i>

		{block name='frontend_index_search_field'}
        	<input type="search" name="sSearch" class="main-search--field" autocomplete="off" placeholder="{s name="IndexSearchFieldPlaceholder"}Bitte geben Sie Ihren Suchbegriff ein...{/s}" maxlength="30"  />
		{/block}

		{block name='frontend_index_search_field_submit'}
        	<input type="submit" class="main-search--button" value="{s name="IndexSearchFieldSubmit"}Suchen{/s}" />
		{/block}
    </form>

    {* Ajax loader *}
	{block name='frontend_index_search_ajax_loader'}
		<div class="ajax-loader">&nbsp;</div>
	{/block}
{/block}