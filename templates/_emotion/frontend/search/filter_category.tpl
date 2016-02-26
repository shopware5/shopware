
{* Search filter options *}
{block name='frontend_search_filter_category_box'}
<div class="category_filter">

	{* Headline *}
	<h3>{se name='SearchFilterCategoryHeading'}Suchergebnis nach Kategorien einschr&auml;nken{/se}</h3>
	
	<div class="categories">
	    {foreach from=$sCategoriesTree key=sKey item=sCategorie}
	        {if $sKey != $sSearchResults.sLastCategory}
	        	<a href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}" class="active">
	            	{$sCategorie.description} &raquo;
	            </a>
	            
	        {else}
	        	<span>{$sCategorie.description}</span>
	        	<div class="clear">&nbsp;</div>
	        {/if}
	    {/foreach}
	    {if $sRequests.sFilter.category}
	    	<a href="{$sLinks.sFilter.category}" class="showall">{se name='SearchFilterLinkDefault'}{/se}</a>
			<div class="space border">&nbsp;</div>
			<div class="space">&nbsp;</div>
	    {/if}
	    {if $sSearchResults.sCategories.0}
	    	{partition assign=sCategoriesParts array=$sSearchResults.sCategories parts=2}
	   
	        {foreach from=$sCategoriesParts item=sCategories}
	            <ul>
	                {foreach from=$sCategories item=sCategorie}
		                {if $sCategorie.count!=""}
		                    <li>
		                    	<a href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}">
						{$sCategorie.description}
		                    	</a>
		                    </li>
		                {/if}
	                {/foreach}
	            </ul>
	        {/foreach}
	    {/if}
		<div class="space">&nbsp;</div>
    </div>
</div>
{/block}
<div class="space">&nbsp;</div>
