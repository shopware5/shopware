{* Search container *}
{block name='frontend_index_search_container'}
<div id="searchcontainer" class="grid_20">
	<div class="inner_searchcontainer">
		<form action="{url controller='search' fullPath=false}" method="get" id="searchform">
			{block name="frontend_index_search_innerform"}{/block}
			<input type="text" name="sSearch" id="searchfield" autocomplete="off" value="{s name="IndexSearchFieldValue"}Suche:{/s}" maxlength="30"  />
			<input type="submit" id="submit_search_btn" value="Suchen" />
		</form>
		
		{* Ajax loader *}
		<div class="ajax_loader">&nbsp;</div>
	</div>
</div>
{/block}