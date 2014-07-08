{* Maincategories left *}
{function name=categories level=0}
	<ul class="sidebar--navigation categories--navigation navigation--list{if !$level} is--drop-down{/if} is--level{$level}" role="menu">
		{* @deprecated The block "frontend_index_categories_left_ul" will be removed in further versions, please use "frontend_index_categories_left_before" *}
		{block name="frontend_index_categories_left_ul"}{/block}

		{block name="frontend_index_categories_left_before"}{/block}
			{foreach $categories as $category}
				{block name="frontend_index_categories_left_entry"}
					<li class="navigation--entry{if $category.flag} is--active{/if}{if $category.subcategories} has--sub-categories{/if}" role="menuitem">
						<a href="{$category.link}" class="navigation--link{if $category.flag} is--active{/if}{if $category.subcategories} has--sub-categories{/if}" title="{$category.description}">
							{$category.description}
						</a>
						{block name="frontend_index_categories_left_entry_subcategories"}
							{if $category.subcategories}
								{call name=categories categories=$category.subcategories level=$level+1}
							{/if}
						{/block}
					</li>
				{/block}
			{/foreach}
		{block name="frontend_index_categories_left_after"}{/block}
	</ul>
{/function}

{if $sCategories}
	{call name=categories categories=$sCategories}
{elseif $sMainCategories}
	{call name=categories categories=$sMainCategories}
{/if}