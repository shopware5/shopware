
{extends file="frontend/search/index.tpl"}

{block name='frontend_search_index_headline'}
	<h2>{s name="SearchTo"}{/s} &bdquo; {$_GET.sSearchText}&rdquo; {s name="SearchWere"}{/s} {$sSearchResultsNum} {s name="SearchArticlesFound"}{/s}</h2>
{/block}
