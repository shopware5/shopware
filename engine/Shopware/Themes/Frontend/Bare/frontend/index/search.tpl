{* Search container *}
{block name='frontend_index_search_container'}
    <form action="{url controller='search' fullPath=false}" method="get" class="main-search--form">
        {block name="frontend_index_search_innerform"}{/block}

		{block name='frontend_index_search_icon'}
			<i class="icon--search"></i>
		{/block}

		{block name='frontend_index_search_field'}
        	<input type="search" name="sSearch" class="main-search--field" autocomplete="off" placeholder="{s name="IndexSearchFieldPlaceholder"}Suchbegriff...{/s}" maxlength="30"  />
		{/block}

		{block name='frontend_index_search_field_submit'}
        	<input type="submit" class="main-search--button" value="1{s name="IndexSearchFieldSubmit"}Suchen{/s}" />
            <div class="loading-indicator"></div>
		{/block}
    </form>

    {* Ajax loader *}
	{block name='frontend_index_search_ajax_loader'}
		<div class="ajax-loader">&nbsp;</div>
	{/block}

    {* Search results *}
    {block name='frontend_index_search_results'}
        <div class="main-search--results"></div>
    {/block}
{/block}