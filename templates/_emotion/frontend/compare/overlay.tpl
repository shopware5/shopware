
<div class="heading">
	{* Headline *}
	<h2>{se name='CompareHeader'}{/se}</h2>

	<a href="#close_compare" onclick="$.compare.hideCompareList()" class="modal_close" title="{s name='LoginActionClose'}{/s}">
		{s name='CompareActionClose'}{/s}
	</a>
</div>
<div class="space">&nbsp;</div>
<div class="inner_container">
	{include file="frontend/compare/col_description.tpl" sArticle=$sComparison.articles sProperties=$sComparison.properties}
	
	{foreach from=$sComparisonsList.articles item=sComparison key=key name="counter"}
		{include file="frontend/compare/col.tpl" sArticle=$sComparison sProperties=$sComparison.properties}
	{/foreach}
</div>
<div class="clear">&nbsp;</div>
