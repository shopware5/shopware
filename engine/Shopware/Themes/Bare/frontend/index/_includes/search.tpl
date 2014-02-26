{* Search container *}
{block name='frontend_index_search_container'}
    <form action="{url controller='search' fullPath=false}" method="get" class="main-search--form">
        {block name="frontend_index_search_innerform"}{/block}
        <input type="search" name="sSearch" class="main-search--field" autocomplete="off" placeholder="{s name="IndexSearchFieldValue"}Suche:{/s}" maxlength="30"  />
        <input type="submit" class="main-search--button" value="Suchen" />
    </form>

    {* Ajax loader *}
    <div class="ajax-loader">&nbsp;</div>
{/block}