{* Maincategories left *}
{function name=categories level=0}
	<ul class="sidebar--navigation navigation--list{if !$level} is--drop-down{/if} is--level{$level}" role="menu">
		{block name="frontend_index_categories_left_ul"}{/block}
		{foreach from=$categories item=category}
			<li class="navigation--entry{if $category.flag} is--active{/if}{if $category.subcategories} has--sub-categories{/if}" role="menuitem">
				<a href="{$category.link}" class="navigation--link{if $category.flag} is--active{/if}{if $category.subcategories} has--sub-categories{/if}" title="{$category.description}">
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