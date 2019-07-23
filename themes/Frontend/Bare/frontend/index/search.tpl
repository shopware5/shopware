{* Search container *}
{block name='frontend_index_search_container'}
    <form action="{url controller='search' fullPath=false}" method="get" class="main-search--form">
        {block name="frontend_index_search_innerform"}{/block}

        {* Search icon *}
        {block name='frontend_index_search_icon'}

        {/block}

        {* Search field *}
        {block name='frontend_index_search_field'}
            <input type="search" name="sSearch" aria-label="{s name="IndexSearchFieldPlaceholder"}{/s}" class="main-search--field" autocomplete="off" autocapitalize="off" placeholder="{s name="IndexSearchFieldPlaceholder"}{/s}" maxlength="30"  />
        {/block}

        {* Search input *}
        {block name='frontend_index_search_field_submit'}
            <button type="submit" class="main-search--button" aria-label="{s name="IndexSearchFieldSubmit"}{/s}">

                {* Search icon *}
                {block name='frontend_index_search_field_submit_icon'}
                    <i class="icon--search"></i>
                {/block}

                {* Search text *}
                {block name='frontend_index_search_field_submit_text'}
                    <span class="main-search--text">{s name="IndexSearchFieldSubmit"}{/s}</span>
                {/block}
            </button>
        {/block}

        {* Ajax loader *}
        {block name='frontend_index_search_ajax_loader'}
            <div class="form--ajax-loader">&nbsp;</div>
        {/block}
    </form>

    {* Search results *}
    {block name='frontend_index_search_results'}
        <div class="main-search--results"></div>
    {/block}
{/block}
