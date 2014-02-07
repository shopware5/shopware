{* Maincategories left *}
{function name=categories level=0}
	<ul class="{if !$level}categories{else}submenu{/if} level{$level}">
	{block name="frontend_index_categories_left_ul"}{/block}
	{foreach from=$categories item=category}
	    <li {if $category.flag || $category.subcategories}class="{if $category.flag or $category.subcategories}active{if $category.subcategories} sub{/if}{/if}"{/if}>
			<a href="{$category.link}" {if $category.flag || $category.subcategories}class="{if $category.flag or $category.subcategories}flag{if $category.subcategories} active{/if}{/if}"{/if}>
				{$category.description}
			</a>
	    	{if $category.subcategories}
		     	{call name=categories categories=$category.subcategories level=$level+1}
		    {/if}
	    </li>
	{/foreach}
	</ul>
{/function}

{if $sCategories}
	{call name=categories categories=$sCategories}
{elseif $sMainCategories}
	{call name=categories categories=$sMainCategories}
{/if}

<div class="left_categories_shadow"></div>