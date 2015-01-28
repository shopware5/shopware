{namespace name=backend/search/index}
{block name="backend/search/index"}
<div class="search-wrapper">
	<div class="arrow-top"></div>
	{if !$searchResult.articles && !$searchResult.customers && !$searchResult.orders}
		{block name="backend/search/index/result_empty_header"}
			<div class="header">
				<div class="inner">
					{s name="title/empty_search"}No search results{/s}
				</div>
			</div>
		{/block}
		
		{block name="backend/search/index/result_empty_content"}
			<div class="result-container">
				<div class="empty">{s name="item/empty"}No search results{/s}</div>
			</div>
		{/block}
	{else}
		{foreach $searchResult as $group => $result}
			{if $result}
				{block name="backend/search/index/result_total"}
					<div class="row {$group}">
					
						{block name="backend/search/index/result_header"}
							<div class="header">
								<div class="inner">
									{if $group === 'articles'}
										{s name="title/articles"}Article{/s}:
									{elseif $group === 'customers'}
										{s name="title/customers"}Customers{/s}:
									{else}
										{s name="title/orders"}Orders{/s}:
									{/if}
								</div>
							</div>
						{/block}
						
						{block name="backend/search/index/result_content"}
							<div class="result-container">
								{foreach $result as $item}
									<a onclick="openSearchResult('{$group}', {$item.id});return false;" href="#"{if $item@iteration is odd by 2} class="odd"{/if}>
										{if $group === 'orders'}{s name="item/order"}Order{/s} - <span class="right">{$item.totalAmount|currency}</span>{/if}<span class="name" style="display:inline-block;width: 155px">{$item.name|truncate:60}</span>
										{if $group === 'articles' && $item.ordernumber}<span class="right">{$item.ordernumber}</span>{/if}
										
										{if $item.description}
											<span class="desc">{$item.description|truncate:45}</span>
										{elseif $item.description_long}
											<span class="desc">{$item.description_long|strip_tags|truncate:45}</span>
										{/if}
									</a>
								{/foreach}
							</div>
						{/block}
					</div>
				{/block}
			{/if}
		{/foreach}
	{/if}
</div>
{/block}